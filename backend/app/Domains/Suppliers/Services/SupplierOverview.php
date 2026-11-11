<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use App\Domains\Suppliers\Models\SupplierSyncRun;
use App\Domains\Suppliers\Models\SupplierTransaction;

/**
 * Reads for the admin supplier pages (M14, Figma "Admin Suppliers" and "Supplier Detail"): the
 * capability matrix declared by each adapter, health and circuit-breaker state, cached balances,
 * recent health checks and sync runs. No supplier API is called; credentials appear only as
 * configured or not.
 */
class SupplierOverview
{
    public function __construct(
        private SupplierAdapterRegistry $registry,
        private SupplierAdminService $admin,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $rates,
    ) {}

    /**
     * Summary cards above the supplier list.
     *
     * @return array{active: int, pending_verification: int, lowest_balance: ?string, low_balance: int}
     */
    public function summary(): array
    {
        $suppliers = Supplier::query()->get(['id', 'status', 'last_balance', 'balance_currency', 'low_balance_threshold']);
        $known = $suppliers->filter(static fn (Supplier $supplier): bool => $supplier->last_balance !== null);
        $lowest = $known->sortBy(static fn (Supplier $supplier): string => str_pad((string) $supplier->last_balance, 30, '0', STR_PAD_LEFT))->first();

        return [
            'active' => $suppliers->filter(static fn (Supplier $supplier): bool => $supplier->status?->value === 'active')->count(),
            'pending_verification' => SupplierTransaction::query()->where('status', SupplierTransactionStatus::PendingVerification)->count(),
            'lowest_balance' => $lowest === null ? null : $this->money($lowest->last_balance, $lowest->balance_currency),
            'low_balance' => $known->filter(static fn (Supplier $supplier): bool => $supplier->low_balance_threshold !== null
                && bccomp((string) $supplier->last_balance, (string) $supplier->low_balance_threshold, 6) === -1)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Supplier $supplier): array
    {
        $all = Supplier::query()->orderBy('priority')->orderBy('id')->get(['id', 'name', 'adapter_key']);
        $matrix = [];
        $columns = [];

        foreach ($all as $row) {
            $columns[] = ['id' => $row->id, 'name' => $row->name, 'current' => $row->id === $supplier->id];
            $levels = $this->registry->has($row->adapter_key) ? $this->admin->capabilities($row)['capabilities'] : [];

            foreach (SupplierCapability::cases() as $capability) {
                $matrix[$capability->value][$row->id] = $levels[$capability->value] ?? 'none';
            }
        }

        $circuitOpen = $supplier->circuit_open_until !== null && $supplier->circuit_open_until->isFuture();

        return [
            'supplier' => $supplier,
            'fallback' => $supplier->fallback_supplier_id === null ? null : $all->firstWhere('id', $supplier->fallback_supplier_id)?->name,
            'fallback_options' => $all->where('id', '!=', $supplier->id)->pluck('name', 'id')->all(),
            'matrix' => $matrix,
            'matrix_columns' => $columns,
            'circuit' => ['open' => $circuitOpen, 'until' => $supplier->circuit_open_until],
            'balance' => $this->money($supplier->last_balance, $supplier->balance_currency),
            'balance_at' => $supplier->last_balance_at,
            'low_balance_threshold' => $this->money($supplier->low_balance_threshold, $supplier->balance_currency),
            'per_request_cap' => $this->money($supplier->per_request_cap_base, $this->rates->baseCurrency()),
            'credentials' => $this->admin->credentialState($supplier),
            'credential_fields' => (array) config("admin.credential_fields.suppliers.{$supplier->adapter_key}", []),
            'health_checks' => SupplierHealthCheck::query()->where('supplier_id', $supplier->id)->latest('id')->limit(5)->get(['id', 'reachable', 'latency_ms', 'error', 'created_at']),
            'sync_runs' => SupplierSyncRun::query()->where('supplier_id', $supplier->id)->latest('id')->limit(5)->get(),
            'pending_verification' => SupplierTransaction::query()->where('supplier_id', $supplier->id)->where('status', SupplierTransactionStatus::PendingVerification)->count(),
        ];
    }

    private function money(mixed $amount, ?string $currency): ?string
    {
        if ($amount === null || $currency === null || $currency === '') {
            return null;
        }

        return $this->converter->present(Money::of((string) $amount, $currency))['formatted'];
    }
}
