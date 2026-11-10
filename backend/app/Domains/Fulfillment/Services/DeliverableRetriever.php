<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Exceptions\DuplicateDigitalCode;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Services\OrderProgress;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Suppliers\Enums\SupplierTransactionCodeRetrievalStatus as Retrieval;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Exceptions\DeliverableFetchFailed;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fetches the code of a successful purchase and stores it in the vault. The fetch is a safe read:
 * a failure marks `code_retrieval_status = failed` and is retried later as a GET. It never
 * creates a new supplier order (architecture 8.3).
 */
class DeliverableRetriever
{
    public function __construct(
        private SupplierAdapterRegistry $registry,
        private DigitalCodeVault $vault,
        private AttemptLocks $locks,
        private UnitStatusRecorder $units,
        private OrderProgress $progress,
    ) {}

    /**
     * @return bool true when the code is now stored
     */
    public function retrieve(int $supplierTransactionId): bool
    {
        $transaction = SupplierTransaction::query()->with('supplier')->find($supplierTransactionId);

        if ($transaction === null || $transaction->status !== Status::Succeeded
            || ! in_array($transaction->code_retrieval_status, [Retrieval::Pending, Retrieval::Failed], true)
            || $transaction->external_transaction_id === null) {
            return false;
        }

        try {
            $payload = $this->registry->for($transaction->supplier)->fetchDeliverables($transaction->external_transaction_id);
        } catch (DeliverableFetchFailed $exception) {
            SupplierTransaction::query()->whereKey($transaction->id)->update(['code_retrieval_status' => Retrieval::Failed, 'updated_at' => now()]);
            Log::warning('suppliers.deliverable_fetch_failed', ['supplier_transaction_id' => $transaction->id, 'http_status' => $exception->httpStatus]);

            return false;
        }

        return DB::transaction(function () use ($transaction, $payload): bool {
            [$order, $unit, $locked] = $this->locks->lock($transaction->id) ?? [null, null, null];

            if ($locked === null || $locked->code_retrieval_status === Retrieval::Retrieved) {
                return false;
            }

            if (count($payload->items) !== 1) {
                Log::alert('suppliers.unexpected_card_count', ['supplier_transaction_id' => $locked->id, 'count' => count($payload->items)]);
            }

            try {
                foreach ($payload->items as $index => $item) {
                    $this->vault->store($item, DigitalCodeSource::Supplier, [
                        'order_item_unit_id' => $index === 0 ? $unit->id : null,
                        'supplier_transaction_id' => $locked->id,
                    ]);
                }
            } catch (DuplicateDigitalCode) {
                Log::alert('suppliers.duplicate_code_delivered', ['supplier_transaction_id' => $locked->id]);
                $locked->forceFill(['code_retrieval_status' => Retrieval::Failed, 'outcome_reason' => 'duplicate_code'])->save();
                $this->units->move($unit, Unit::ManualReview, 'duplicate_code', 'The supplier delivered a code that already exists.', ['supplier_transaction_id' => $locked->id]);
                $this->progress->advance($order, OrderActor::system());

                return false;
            }

            $locked->forceFill(['code_retrieval_status' => Retrieval::Retrieved])->save();
            $this->units->move($unit, Unit::Delivered, 'delivered', 'Code stored in the vault.', ['supplier_transaction_id' => $locked->id]);
            $this->progress->advance($order, OrderActor::system());

            return true;
        }, 3);
    }
}
