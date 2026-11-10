<?php

namespace App\Domains\Mcp\Repositories;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Enums\ManualFulfillmentTaskStatus;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Suppliers\Enums\SupplierSyncRunType;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use App\Domains\Suppliers\Models\SupplierSyncRun;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Read-only supplier and operations queries behind the MCP inventory, supplier and operations
 * tools. Supplier credentials, API payloads and provider error bodies are never selected.
 */
class McpOperationsRepository
{
    private const array SUPPLIER_COLUMNS = ['id', 'name', 'adapter_key', 'status', 'mode', 'health', 'circuit_open_until', 'balance_currency', 'last_balance', 'last_balance_at', 'low_balance_threshold', 'priority'];

    /**
     * @return Collection<int, Supplier>
     */
    public function suppliers(?string $supplier = null): Collection
    {
        return Supplier::query()
            ->select(self::SUPPLIER_COLUMNS)
            ->when($supplier !== null, fn (Builder $query) => $this->matchSupplier($query, (string) $supplier))
            ->orderBy('priority')
            ->orderBy('id')
            ->get();
    }

    public function findSupplier(string $supplier): ?Supplier
    {
        return $this->matchSupplier(Supplier::query()->select(self::SUPPLIER_COLUMNS), $supplier)->first();
    }

    /**
     * The latest health check per supplier.
     *
     * @param  list<int>  $supplierIds
     * @return SupportCollection<int, SupplierHealthCheck>
     */
    public function latestHealthChecks(array $supplierIds): SupportCollection
    {
        $latest = SupplierHealthCheck::query()->selectRaw('MAX(id)')->whereIn('supplier_id', $supplierIds)->groupBy('supplier_id');

        return SupplierHealthCheck::query()
            ->select(['id', 'supplier_id', 'reachable', 'latency_ms', 'created_at'])
            ->whereIn('id', $latest)
            ->get()
            ->keyBy('supplier_id');
    }

    /**
     * The latest catalog and pricing run per supplier, keyed "{supplier_id}:{type}".
     *
     * @param  list<int>  $supplierIds
     * @return SupportCollection<string, SupplierSyncRun>
     */
    public function latestSyncRuns(array $supplierIds): SupportCollection
    {
        $latest = SupplierSyncRun::query()
            ->selectRaw('MAX(id)')
            ->whereIn('supplier_id', $supplierIds)
            ->whereIn('type', [SupplierSyncRunType::Catalog, SupplierSyncRunType::Pricing])
            ->groupBy('supplier_id', 'type');

        return SupplierSyncRun::query()
            ->select(['id', 'supplier_id', 'type', 'triggered_by', 'status', 'items_seen', 'items_created', 'items_updated', 'items_failed', 'error', 'started_at', 'finished_at'])
            ->whereIn('id', $latest)
            ->get()
            ->keyBy(static fn (SupplierSyncRun $run): string => $run->supplier_id.':'.$run->type->value);
    }

    public function availableStockCount(ProductVariant $variant): int
    {
        return DigitalCode::query()
            ->where('product_variant_id', $variant->id)
            ->where('stock_status', DigitalCodeStockStatus::Available)
            ->whereNull('order_item_unit_id')
            ->count();
    }

    /**
     * Failed payment attempts created in the window, with the order number and gateway key.
     *
     * @return LengthAwarePaginator<int, Payment>
     */
    public function failedPaymentsPage(CarbonImmutable $from, CarbonImmutable $to, int $page, int $perPage): LengthAwarePaginator
    {
        return Payment::query()
            ->select(['id', 'public_id', 'order_id', 'payment_gateway_id', 'attempt_no', 'status', 'amount', 'currency', 'failure_code', 'created_at', 'updated_at'])
            ->with(['order:id,order_number,status', 'paymentGateway:id,key'])
            ->whereIn('status', [PaymentStatus::Failed, PaymentStatus::Expired, PaymentStatus::Cancelled])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * Open reconciliation items; `details` is not selected (it can hold provider payloads).
     *
     * @return LengthAwarePaginator<int, ReconciliationItem>
     */
    public function openReconciliationPage(int $page, int $perPage): LengthAwarePaginator
    {
        return ReconciliationItem::query()
            ->select(['id', 'reconciliation_run_id', 'type', 'reference_type', 'reference_id', 'status', 'created_at'])
            ->with('reconciliationRun:id,kind,subject_key,window_start,window_end')
            ->where('status', ReconciliationItemStatus::Open)
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }

    public function unresolvedSupplierAttempts(): int
    {
        return SupplierTransaction::query()
            ->whereIn('status', [SupplierTransactionStatus::Submitting, SupplierTransactionStatus::Accepted, SupplierTransactionStatus::PendingVerification])
            ->count();
    }

    public function openManualTasks(): int
    {
        return ManualFulfillmentTask::query()->whereIn('status', [ManualFulfillmentTaskStatus::Open, ManualFulfillmentTaskStatus::Claimed])->count();
    }

    /**
     * @return array{pending_jobs: int, failed_jobs: int}
     */
    public function queueCounts(): array
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    public function databaseReachable(): bool
    {
        return DB::selectOne('SELECT 1 AS ok')?->ok == 1;
    }

    /**
     * @param  Builder<Supplier>  $query
     * @return Builder<Supplier>
     */
    private function matchSupplier(Builder $query, string $supplier): Builder
    {
        return ctype_digit($supplier)
            ? $query->whereKey((int) $supplier)
            : $query->where(static fn (Builder $match) => $match->where('name', $supplier)->orWhere('adapter_key', Str::lower($supplier)));
    }
}
