<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Jobs\RetrieveDeliverables;
use App\Domains\Fulfillment\Jobs\SubmitUnitToSupplier;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderProgress;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\Enums\SupplierTransactionCodeRetrievalStatus as Retrieval;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Recovery for interrupted workers (architecture 8.2). A `submitting` attempt older than its
 * supplier's request timeout plus a grace period may or may not have reached the supplier, so it
 * becomes `pending_verification`; it is never resubmitted. A `queued` unit that never reached
 * the gate is dispatched again, which is safe because the gate admits one attempt.
 */
class StaleSubmissionSweeper
{
    public function __construct(
        private SupplierOutcomeApplier $applier,
        private AttemptLocks $locks,
        private OrderProgress $progress,
    ) {}

    /**
     * @return array{stale_submissions: int, requeued_units: int, deliverable_retries: int}
     */
    public function sweep(): array
    {
        $grace = (int) config('suppliers.stale_submission_grace_seconds');
        $stale = 0;

        $candidates = SupplierTransaction::query()->with('supplier')
            ->where('status', Status::Submitting)
            ->where('submitted_at', '<', now()->subSeconds($grace))
            ->orderBy('id')->limit(500)->get();

        foreach ($candidates as $transaction) {
            $timeout = intdiv($transaction->supplier->request_timeout_ms, 1000) + $grace;

            if ($transaction->submitted_at->gt(now()->subSeconds($timeout))) {
                continue;
            }

            $stale += $this->markStale($transaction->id) ? 1 : 0;
        }

        $requeued = OrderItemUnit::query()->where('status', Unit::Queued)
            ->where('updated_at', '<', now()->subMinutes((int) config('suppliers.requeue_stuck_after_minutes')))
            ->limit(500)->pluck('id');
        $requeued->each(static fn (int $id) => SubmitUnitToSupplier::dispatch($id));

        $retries = SupplierTransaction::query()->where('status', Status::Succeeded)
            ->where('code_retrieval_status', Retrieval::Failed)
            ->where('updated_at', '<', now()->subMinutes((int) config('suppliers.deliverable_retry_after_minutes')))
            ->limit(500)->pluck('id');
        $retries->each(static fn (int $id) => RetrieveDeliverables::dispatch($id));

        return ['stale_submissions' => $stale, 'requeued_units' => $requeued->count(), 'deliverable_retries' => $retries->count()];
    }

    private function markStale(int $transactionId): bool
    {
        return DB::transaction(function () use ($transactionId): bool {
            [$order, $unit, $transaction] = $this->locks->lock($transactionId) ?? [null, null, null];

            if ($transaction === null || $transaction->status !== Status::Submitting) {
                return false;
            }

            $this->applier->apply($order, $unit, $transaction, SupplierOrderOutcome::ambiguous('stale_submission'));
            $transaction->forceFill(['next_verification_at' => now()])->save();
            $this->progress->advance($order, OrderActor::system());

            return true;
        }, 3);
    }
}
