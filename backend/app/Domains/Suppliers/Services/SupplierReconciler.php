<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Enums\ReconciliationItemType;
use App\Domains\Payments\Enums\ReconciliationRunKind;
use App\Domains\Payments\Enums\ReconciliationRunStatus;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\ReconciliationRun;
use App\Domains\Suppliers\DTOs\SupplierTransactionRecord;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierOutcomeKind;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * `suppliers:reconcile-daily` (architecture 8.3). Compares the supplier's transaction report for
 * the window with our attempts and raises reconciliation_items for orphans (theirs, unknown to
 * us), attempts missing from their report, status mismatches and cost differences. It reports
 * only: it never changes an order or an attempt.
 */
class SupplierReconciler
{
    private const int MAX_PAGES = 200;

    public function __construct(private SupplierAdapterRegistry $registry) {}

    public function run(Supplier $supplier, CarbonImmutable $windowEnd): ?ReconciliationRun
    {
        $adapter = $this->registry->for($supplier);

        if (! $adapter->capabilities()->supports(SupplierCapability::TransactionHistory)) {
            return null;
        }

        $windowStart = $windowEnd->subHours((int) config('suppliers.reconciliation_window_hours'));

        try {
            $run = (new ReconciliationRun)->forceFill([
                'kind' => ReconciliationRunKind::Suppliers,
                'subject_key' => 'supplier:'.$supplier->id,
                'window_start' => $windowStart,
                'window_end' => $windowEnd,
                'status' => ReconciliationRunStatus::Running,
                'started_at' => now(),
            ]);
            $run->save();
        } catch (UniqueConstraintViolationException) {
            return null;
        }

        $records = [];
        $complete = false;

        try {
            for ($page = 1; $page <= self::MAX_PAGES; $page++) {
                $result = $adapter->getTransactions($windowStart, $windowEnd, $page);
                $records = [...$records, ...$result->records];

                if ($result->isLast) {
                    $complete = true;

                    break;
                }
            }
        } catch (SupplierRequestFailed $exception) {
            $run->forceFill(['status' => ReconciliationRunStatus::Failed, 'finished_at' => now()])->save();

            return $run;
        }

        $mismatches = 0;
        $seen = [];

        foreach ($records as $record) {
            $ours = SupplierTransaction::query()->where('supplier_id', $supplier->id)
                ->where(static fn ($query) => $query->where('external_transaction_id', $record->externalTransactionId)
                    ->when($record->clientReference !== null, static fn ($inner) => $inner->orWhere('client_reference', $record->clientReference)))
                ->orderByDesc('attempt_no')->first();

            if ($ours === null) {
                $mismatches += $this->item($run, ReconciliationItemType::OrphanProvider, null, $record, ['supplier_status' => $record->supplierStatus]);

                continue;
            }

            $seen[] = $ours->id;

            if ($this->statusDiffers($ours, $record)) {
                $mismatches += $this->item($run, ReconciliationItemType::StatusMismatch, $ours, $record, ['ours' => $ours->status->value, 'supplier_status' => $record->supplierStatus]);
            }

            if ($record->cost !== null && $ours->actual_cost !== null && ($record->cost->currency !== $ours->actual_cost_currency || bccomp($record->cost->amount, $ours->actual_cost, 6) !== 0)) {
                $mismatches += $this->item($run, ReconciliationItemType::AmountMismatch, $ours, $record, ['ours' => $ours->actual_cost.' '.$ours->actual_cost_currency, 'supplier' => (string) $record->cost]);
            }
        }

        if ($complete) {
            $missing = SupplierTransaction::query()->where('supplier_id', $supplier->id)->where('status', Status::Succeeded)
                ->whereBetween('submitted_at', [$windowStart, $windowEnd])->whereNotIn('id', $seen === [] ? [0] : $seen)->get();

            foreach ($missing as $ours) {
                $mismatches += $this->item($run, ReconciliationItemType::MissingProvider, $ours, null, ['ours' => $ours->status->value]);
            }
        }

        $run->forceFill(['status' => ReconciliationRunStatus::Completed, 'checked' => count($records), 'mismatches' => $mismatches, 'finished_at' => now()])->save();

        return $run;
    }

    private function statusDiffers(SupplierTransaction $ours, SupplierTransactionRecord $record): bool
    {
        return match ($record->kind) {
            SupplierOutcomeKind::Fulfilled => $ours->status !== Status::Succeeded,
            SupplierOutcomeKind::Rejected => in_array($ours->status, [Status::Succeeded, Status::Accepted], true),
            SupplierOutcomeKind::InProgress => in_array($ours->status, [Status::Succeeded, Status::Rejected, Status::NotAcceptedVerified], true),
            default => true,
        };
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function item(ReconciliationRun $run, ReconciliationItemType $type, ?SupplierTransaction $ours, ?SupplierTransactionRecord $record, array $details): int
    {
        (new ReconciliationItem)->forceFill([
            'reconciliation_run_id' => $run->id,
            'type' => $type,
            'reference_type' => $ours?->getMorphClass(),
            'reference_id' => $ours?->id,
            'provider_reference' => $record?->externalTransactionId ?? $ours?->external_transaction_id,
            'details' => $details,
            'status' => ReconciliationItemStatus::Open,
        ])->save();

        return 1;
    }
}
