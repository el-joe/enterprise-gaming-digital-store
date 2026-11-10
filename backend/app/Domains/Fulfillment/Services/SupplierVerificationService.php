<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderProgress;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierOutcomeKind as Kind;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * `suppliers:verify-pending` for one attempt (architecture 8.3). The supplier is asked, by its id
 * when known or by our client reference, with no transaction open; the answer is applied only if
 * the attempt has not moved meanwhile. Not found, or an unreadable answer, keeps polling until
 * the deadline and then holds the unit for review with the active lock retained. Nothing here
 * ever places an order.
 */
class SupplierVerificationService
{
    public function __construct(
        private SupplierAdapterRegistry $registry,
        private SupplierOutcomeApplier $applier,
        private AttemptLocks $locks,
        private OrderProgress $progress,
    ) {}

    /**
     * `$actor` (staff reconciling) is used only to release an order held in manual review; every
     * other derived move is the system's.
     */
    public function verify(int $supplierTransactionId, bool $force = false, ?OrderActor $actor = null): void
    {
        $transaction = SupplierTransaction::query()->with('supplier')->find($supplierTransactionId);

        if ($transaction === null || ! in_array($transaction->status, [Status::PendingVerification, Status::Accepted], true)) {
            return;
        }

        if (! $force && ($transaction->next_verification_at === null || $transaction->next_verification_at->isFuture())) {
            return;
        }

        $outcome = $this->ask($transaction);

        DB::transaction(function () use ($transaction, $outcome, $actor): void {
            [$order, $unit, $locked] = $this->locks->lock($transaction->id) ?? [null, null, null];

            if ($locked === null || $locked->status !== $transaction->status || $locked->verification_attempts !== $transaction->verification_attempts) {
                return;
            }

            $locked->forceFill(['verification_attempts' => $locked->verification_attempts + 1])->save();

            if ($outcome === null) {
                $this->applier->holdForReview($unit, $locked, 'no_lookup_capability');
            } elseif (in_array($outcome->kind, [Kind::Fulfilled, Kind::InProgress, Kind::Rejected], true)) {
                $this->applier->apply($order, $unit, $locked, $outcome);
            } else {
                $this->stillUnknown($unit, $locked, $outcome);
            }

            $this->progress->advance($order, $actor !== null && $order->status === OrderStatus::ManualReview ? $actor : OrderActor::system());
        }, 3);
    }

    /**
     * @return SupplierOrderOutcome|null null when the supplier cannot be asked at all
     */
    private function ask(SupplierTransaction $transaction): ?SupplierOrderOutcome
    {
        $adapter = $this->registry->for($transaction->supplier);
        $capabilities = $adapter->capabilities();

        try {
            if ($transaction->external_transaction_id !== null && $capabilities->supports(SupplierCapability::OrderStatus)) {
                return $adapter->getOrderStatus($transaction->external_transaction_id);
            }

            if ($capabilities->supports(SupplierCapability::LookupByReference)) {
                return $adapter->findOrderByReference($transaction->client_reference, $transaction->submitted_at ?? $transaction->created_at);
            }
        } catch (Throwable $exception) {
            report($exception);

            return SupplierOrderOutcome::ambiguous('lookup_exception');
        }

        return null;
    }

    private function stillUnknown(OrderItemUnit $unit, SupplierTransaction $transaction, SupplierOrderOutcome $outcome): void
    {
        $transaction->forceFill(['outcome_reason' => 'verification_'.$outcome->reason])->save();

        if ($transaction->verification_deadline_at !== null && ! $transaction->verification_deadline_at->isFuture()) {
            $this->applier->holdForReview($unit, $transaction, 'deadline_passed');

            return;
        }

        $this->applier->scheduleNextCheck($transaction);
    }
}
