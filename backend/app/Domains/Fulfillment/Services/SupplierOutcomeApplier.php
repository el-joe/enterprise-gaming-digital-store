<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Jobs\RetrieveDeliverables;
use App\Domains\Fulfillment\Jobs\SubmitUnitToSupplier;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Suppliers\DTOs\SelectionResult;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierOutcomeKind as Kind;
use App\Domains\Suppliers\Enums\SupplierTransactionCodeRetrievalStatus as Retrieval;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use App\Domains\Suppliers\Services\SupplierSelector;
use Illuminate\Support\Facades\Log;

/**
 * Applies a classified outcome to one attempt and its unit (architecture 7.2, 7.4, 8.3). Callers
 * hold the order, unit and transaction locks, in that order, inside one transaction. Follow-up
 * jobs are dispatched after commit.
 *
 * The only path back to a supplier order is a Rejected outcome passing the fallback rules.
 * Ambiguous never leads to createOrder.
 */
class SupplierOutcomeApplier
{
    public function __construct(
        private SupplierAdapterRegistry $registry,
        private SupplierSelector $selector,
        private UnitStatusRecorder $units,
    ) {}

    public function apply(Order $order, OrderItemUnit $unit, SupplierTransaction $transaction, SupplierOrderOutcome $outcome): void
    {
        if ($outcome->externalTransactionId !== null && $this->externalIdTaken($transaction, $outcome->externalTransactionId)) {
            Log::alert('suppliers.duplicate_external_reference', ['supplier_transaction_id' => $transaction->id, 'supplier_id' => $transaction->supplier_id]);
            $outcome = SupplierOrderOutcome::ambiguous('duplicate_external_reference');
        }

        match ($outcome->kind) {
            Kind::Fulfilled => $this->fulfilled($unit, $transaction, $outcome),
            Kind::InProgress => $this->inProgress($unit, $transaction, $outcome),
            Kind::Rejected => $this->rejected($order, $unit, $transaction, $outcome),
            Kind::Ambiguous, Kind::NotFound => $this->ambiguous($unit, $transaction, $outcome),
        };
    }

    /**
     * Verification gave up: deadline passed, or the supplier cannot be asked. The attempt keeps
     * its active lock; a human decides (architecture 8.3).
     */
    public function holdForReview(OrderItemUnit $unit, SupplierTransaction $transaction, string $reason): void
    {
        $transaction->forceFill(['next_verification_at' => null, 'outcome_reason' => $reason])->save();

        if ($unit->status !== Unit::ManualReview) {
            $this->units->move($unit, Unit::ManualReview, 'verification_'.$reason, 'Supplier outcome could not be verified; staff must reconcile.', ['supplier_transaction_id' => $transaction->id]);
        }
    }

    public function scheduleNextCheck(SupplierTransaction $transaction): void
    {
        $backoff = config('suppliers.verification_backoff_minutes');
        $minutes = $backoff[min($transaction->verification_attempts, count($backoff) - 1)];

        $transaction->forceFill(['next_verification_at' => now()->addMinutes($minutes)])->save();
    }

    private function fulfilled(OrderItemUnit $unit, SupplierTransaction $transaction, SupplierOrderOutcome $outcome): void
    {
        $adapter = $this->registry->for($transaction->supplier);
        $fetches = $adapter->capabilities()->supports(SupplierCapability::FetchDeliverables);

        $transaction->forceFill([
            'status' => Status::Succeeded,
            'external_transaction_id' => $outcome->externalTransactionId ?? $transaction->external_transaction_id,
            'outcome_reason' => $outcome->reason,
            'actual_cost' => $outcome->actualCost?->amount ?? $transaction->actual_cost,
            'actual_cost_currency' => $outcome->actualCost?->currency ?? $transaction->actual_cost_currency,
            'completed_at' => now(),
            'next_verification_at' => null,
            'code_retrieval_status' => match (true) {
                $outcome->vaultCodeId !== null => Retrieval::Retrieved,
                $fetches => Retrieval::Pending,
                default => Retrieval::NotApplicable,
            },
        ])->save();

        if ($outcome->vaultCodeId !== null) {
            DigitalCode::query()->whereKey($outcome->vaultCodeId)->where('supplier_transaction_id', $transaction->id)
                ->update(['order_item_unit_id' => $unit->id, 'stock_status' => DigitalCodeStockStatus::Delivered]);
            $this->units->move($unit, Unit::Delivered, 'delivered', 'Delivered from internal stock.', ['supplier_transaction_id' => $transaction->id]);

            return;
        }

        if ($fetches) {
            $this->units->move($unit, Unit::SupplierPending, 'purchased', 'Supplier purchase succeeded; fetching the code.', ['supplier_transaction_id' => $transaction->id]);
            RetrieveDeliverables::dispatch($transaction->id)->afterCommit();

            return;
        }

        $this->units->move($unit, Unit::Delivered, 'delivered', 'Supplier reports the unit delivered.', ['supplier_transaction_id' => $transaction->id]);
    }

    private function inProgress(OrderItemUnit $unit, SupplierTransaction $transaction, SupplierOrderOutcome $outcome): void
    {
        $transaction->forceFill([
            'status' => Status::Accepted,
            'external_transaction_id' => $outcome->externalTransactionId ?? $transaction->external_transaction_id,
            'outcome_reason' => $outcome->reason,
            'verification_deadline_at' => $transaction->verification_deadline_at ?? $this->deadline($transaction),
        ])->save();
        $this->scheduleNextCheck($transaction);

        if ($unit->status !== Unit::SupplierPending) {
            $this->units->move($unit, Unit::SupplierPending, 'supplier_processing', null, ['supplier_transaction_id' => $transaction->id]);
        }
    }

    private function ambiguous(OrderItemUnit $unit, SupplierTransaction $transaction, SupplierOrderOutcome $outcome): void
    {
        $transaction->forceFill([
            'status' => $transaction->status === Status::Accepted ? Status::Accepted : Status::PendingVerification,
            'external_transaction_id' => $transaction->external_transaction_id ?? $outcome->externalTransactionId,
            'outcome_reason' => $outcome->reason,
            'supplier_error_code' => $outcome->supplierErrorCode,
            'verification_deadline_at' => $transaction->verification_deadline_at ?? $this->deadline($transaction),
        ])->save();

        $capabilities = $this->registry->for($transaction->supplier)->capabilities();
        $canLookUp = $capabilities->supports(SupplierCapability::LookupByReference)
            || ($transaction->external_transaction_id !== null && $capabilities->supports(SupplierCapability::OrderStatus));

        if (! $canLookUp) {
            $this->holdForReview($unit, $transaction, 'no_lookup_capability');

            return;
        }

        $this->scheduleNextCheck($transaction);

        if ($transaction->status === Status::PendingVerification && ! in_array($unit->status, [Unit::PendingVerification, Unit::ManualReview], true)) {
            $this->units->move($unit, Unit::PendingVerification, 'pending_verification', 'Supplier outcome unknown; verifying by reference. Not retried.', [
                'supplier_transaction_id' => $transaction->id,
                'reason' => $outcome->reason,
            ]);
        }
    }

    /**
     * Fallback only on a definitive refusal (architecture 7.4): a pre-send failure may retry the
     * same supplier under its retry policy; otherwise the next candidate, if fallback is enabled,
     * attempts remain and its margin holds. A candidate refused only by the margin guard holds the
     * unit for review; no candidate at all fails the unit.
     */
    private function rejected(Order $order, OrderItemUnit $unit, SupplierTransaction $transaction, SupplierOrderOutcome $outcome): void
    {
        $transaction->forceFill([
            'status' => Status::Rejected,
            'external_transaction_id' => $transaction->external_transaction_id ?? $outcome->externalTransactionId,
            'outcome_reason' => $outcome->reason,
            'supplier_error_code' => $outcome->supplierErrorCode,
            'cost_reversal_pending' => $outcome->costReversalPending,
            'completed_at' => now(),
            'next_verification_at' => null,
        ])->save();

        if ($outcome->reason === 'invalid_request' || $outcome->reason === 'auth_refused' || $outcome->costReversalPending) {
            Log::alert('suppliers.order_refused', ['supplier_transaction_id' => $transaction->id, 'reason' => $outcome->reason, 'error_code' => $outcome->supplierErrorCode]);
        }

        $supplier = $transaction->supplier;
        $attempts = SupplierTransaction::query()->where('order_item_unit_id', $unit->id)->count();

        if ($outcome->notSent && $this->sameSupplierRetryAllowed($unit, $transaction)) {
            $this->units->move($unit, Unit::Queued, 'retry_same_supplier', 'Request was not sent; retrying the same supplier.', ['supplier_transaction_id' => $transaction->id]);
            SubmitUnitToSupplier::dispatch($unit->id)->afterCommit();

            return;
        }

        if (! $supplier->fallback_enabled || $attempts > $supplier->max_fallback_attempts) {
            $this->units->move($unit, Unit::Failed, 'supplier_rejected', 'Supplier refused the order; no fallback permitted.', ['supplier_transaction_id' => $transaction->id, 'reason' => $outcome->reason]);

            return;
        }

        $refused = SupplierTransaction::query()->where('order_item_unit_id', $unit->id)->where('status', Status::Rejected)->pluck('supplier_id')->unique()->values()->all();
        $selection = $this->selector->rank($unit, $order, $refused);
        $unit->forceFill(['selection_snapshot' => $selection->toSnapshot()]);

        if ($selection->first() !== null) {
            $this->units->move($unit, Unit::Queued, 'fallback', 'Supplier refused the order; trying the next supplier.', [
                'supplier_transaction_id' => $transaction->id,
                'next_supplier_id' => $selection->first()->supplierId,
            ]);
            SubmitUnitToSupplier::dispatch($unit->id)->afterCommit();

            return;
        }

        $this->units->move(
            $unit,
            $selection->excludedByMargin() ? Unit::ManualReview : Unit::Failed,
            $selection->excludedByMargin() ? 'fallback_below_margin' : 'fallback_exhausted',
            $selection->excludedByMargin() ? 'The next supplier would sell below margin.' : 'No other supplier can fulfil this unit.',
            ['supplier_transaction_id' => $transaction->id, 'reasons' => array_column($selection->excluded, 'reason'), 'margin' => SelectionResult::BELOW_MARGIN],
        );
    }

    private function sameSupplierRetryAllowed(OrderItemUnit $unit, SupplierTransaction $transaction): bool
    {
        $max = (int) ($transaction->supplier->retry_policy['max_attempts'] ?? 0);

        return $max > 0 && SupplierTransaction::query()
            ->where('order_item_unit_id', $unit->id)
            ->where('supplier_id', $transaction->supplier_id)
            ->where('status', Status::Rejected)
            ->count() < $max;
    }

    private function externalIdTaken(SupplierTransaction $transaction, string $externalId): bool
    {
        return SupplierTransaction::query()
            ->where('supplier_id', $transaction->supplier_id)
            ->where('external_transaction_id', $externalId)
            ->whereKeyNot($transaction->id)
            ->exists();
    }

    private function deadline(SupplierTransaction $transaction): mixed
    {
        return ($transaction->submitted_at ?? now())->copy()->addMinutes($transaction->supplier->verification_deadline_minutes);
    }
}
