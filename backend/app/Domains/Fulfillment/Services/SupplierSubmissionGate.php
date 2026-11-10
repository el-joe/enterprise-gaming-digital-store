<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderProgress;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Suppliers\DTOs\SupplierCandidate;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\DTOs\SupplierOrderRequest;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The exactly-once gate (architecture 8.2):
 *
 * 1. In one transaction: lock the unit; anything but `queued` is a no-op (a redelivered job);
 *    insert the attempt as `submitting` (uq_st_active_unit refuses a second active attempt) and
 *    mark the unit `submitting`. Commit, so a crash afterwards leaves durable evidence.
 * 2. Call the supplier with no transaction open.
 * 3. Lock order, unit and attempt; apply the classified outcome; derive the order status.
 */
class SupplierSubmissionGate
{
    public function __construct(
        private SupplierAdapterRegistry $registry,
        private SupplierOutcomeApplier $applier,
        private AttemptLocks $locks,
        private UnitStatusRecorder $units,
        private OrderProgress $progress,
        private FulfilmentGuard $guard,
        private ExchangeRateRegistry $rates,
    ) {}

    public function submit(int $unitId): void
    {
        $claimed = $this->claim($unitId);

        if ($claimed === null) {
            return;
        }

        [$transaction, $request] = $claimed;

        try {
            $outcome = $this->registry->for($transaction->supplier)->createOrder($request);
        } catch (Throwable $exception) {
            report($exception);
            $outcome = SupplierOrderOutcome::ambiguous('unexpected_exception');
        }

        $this->record($transaction->id, $outcome);
    }

    /**
     * Step 3, also used by tests that simulate a worker dying between steps.
     */
    public function record(int $supplierTransactionId, SupplierOrderOutcome $outcome): void
    {
        DB::transaction(function () use ($supplierTransactionId, $outcome): void {
            $locked = $this->locks->lock($supplierTransactionId);

            if ($locked === null) {
                return;
            }

            [$order, $unit, $transaction] = $locked;

            if (! in_array($transaction->status, [Status::Submitting, Status::PendingVerification], true)) {
                return;
            }

            $this->applier->apply($order, $unit, $transaction, $outcome);
            $this->progress->advance($order, OrderActor::system());
        }, 3);
    }

    /**
     * Step 1.
     *
     * @return array{0: SupplierTransaction, 1: SupplierOrderRequest}|null
     */
    public function claim(int $unitId): ?array
    {
        try {
            return DB::transaction(function () use ($unitId): ?array {
                $unit = OrderItemUnit::query()->lockForUpdate()->find($unitId);

                if ($unit === null || $unit->status !== Unit::Queued) {
                    return null;
                }

                $order = Order::query()->findOrFail($unit->order_id);
                $row = $unit->selection_snapshot['candidates'][0] ?? null;

                if (! $this->guard->allows($order) || ! is_array($row)) {
                    return null;
                }

                $candidate = SupplierCandidate::fromArray($row, $this->rates->baseCurrency());
                $supplier = Supplier::query()->findOrFail($candidate->supplierId);
                $previous = SupplierTransaction::query()->where('order_item_unit_id', $unit->id)->orderBy('attempt_no')->get(['supplier_id', 'attempt_no']);

                $transaction = (new SupplierTransaction)->forceFill([
                    'order_item_unit_id' => $unit->id,
                    'supplier_id' => $supplier->id,
                    'supplier_product_mapping_id' => $candidate->mappingId,
                    'attempt_no' => ((int) $previous->max('attempt_no')) + 1,
                    'client_reference' => 'u'.$unit->public_id.'s'.$supplier->id,
                    'status' => Status::Submitting,
                    'is_fallback' => $previous->isNotEmpty() && $previous->first()->supplier_id !== $supplier->id,
                    'requested_amount' => $candidate->unitPrice,
                    'requested_currency' => $candidate->unitCurrency,
                    'submitted_at' => now(),
                ]);
                $transaction->save();
                $transaction->setRelation('supplier', $supplier);

                $this->units->move($unit, Unit::Submitting, 'submitting', null, ['supplier_transaction_id' => $transaction->id, 'supplier_id' => $supplier->id, 'attempt_no' => $transaction->attempt_no]);

                return [$transaction, new SupplierOrderRequest(
                    clientReference: $transaction->client_reference,
                    supplierProductId: $candidate->supplierProductExternalId,
                    unitPrice: $candidate->unitPrice,
                    unitCurrency: $candidate->unitCurrency,
                    supplierTransactionId: $transaction->id,
                    productVariantId: OrderItem::query()->whereKey($unit->order_item_id)->value('product_variant_id'),
                    deliveryValues: OrderItemDeliveryValue::query()->where('order_item_id', $unit->order_item_id)->get()->mapWithKeys(static fn (OrderItemDeliveryValue $value): array => [$value->field_code => (string) $value->value])->all(),
                )];
            });
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }
}
