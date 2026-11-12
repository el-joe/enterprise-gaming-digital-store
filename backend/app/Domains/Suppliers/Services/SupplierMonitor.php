<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\DTOs\ConnectionResult;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierHealth;
use App\Domains\Suppliers\Exceptions\SupplierNotConfigured;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierBalanceSnapshot;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use Illuminate\Support\Facades\Log;

/**
 * Balance refresh and health checks (`suppliers:refresh-balances`, `suppliers:health-check`). The
 * cached balance feeds selection; repeated failed health checks open the circuit so selection
 * skips the supplier for a while.
 */
class SupplierMonitor
{
    public function __construct(private SupplierAdapterRegistry $registry) {}

    /**
     * Whether this supplier's current environment offers no balance (see config
     * `suppliers.balance_unavailable_in_sandbox`).
     */
    public static function balanceUnavailable(Supplier $supplier): bool
    {
        return $supplier->mode === EnvironmentMode::Sandbox
            && in_array($supplier->adapter_key, (array) config('suppliers.balance_unavailable_in_sandbox', []), true);
    }

    public function refreshBalance(Supplier $supplier): ?SupplierBalanceSnapshot
    {
        if (self::balanceUnavailable($supplier)) {
            return null;
        }

        $adapter = $this->registry->for($supplier);

        if (! $adapter->capabilities()->supports(SupplierCapability::Balance)) {
            return null;
        }

        try {
            $balance = $adapter->getBalance();
        } catch (SupplierRequestFailed|SupplierNotConfigured $exception) {
            Log::warning('suppliers.balance_refresh_failed', ['supplier_id' => $supplier->id, 'error' => $exception->getMessage()]);

            return null;
        }

        $supplier->forceFill([
            'last_balance' => $balance->balance->amount,
            'balance_currency' => $balance->balance->currency,
            'last_balance_at' => $balance->capturedAt,
        ])->save();

        $snapshot = (new SupplierBalanceSnapshot)->forceFill([
            'supplier_id' => $supplier->id,
            'balance' => $balance->balance->amount,
            'currency' => $balance->balance->currency,
            'captured_at' => $balance->capturedAt,
        ]);
        $snapshot->save();

        if ($supplier->low_balance_threshold !== null && bccomp($balance->balance->amount, $supplier->low_balance_threshold, 6) === -1) {
            Log::alert('suppliers.low_balance', ['supplier_id' => $supplier->id, 'balance' => $balance->balance->amount, 'currency' => $balance->balance->currency]);
        }

        return $snapshot;
    }

    public function checkHealth(Supplier $supplier): SupplierHealthCheck
    {
        try {
            $result = $this->registry->for($supplier)->testConnection();
        } catch (SupplierNotConfigured $exception) {
            $result = new ConnectionResult(false, error: $exception->getMessage());
        }

        $check = (new SupplierHealthCheck)->forceFill([
            'supplier_id' => $supplier->id,
            'reachable' => $result->reachable,
            'latency_ms' => $result->latencyMs,
            'error' => $result->error === null ? null : mb_substr($result->error, 0, 250),
        ]);
        $check->save();

        $threshold = (int) config('suppliers.circuit_failure_threshold');
        $recentFailures = SupplierHealthCheck::query()->where('supplier_id', $supplier->id)->latest('id')->limit($threshold)->pluck('reachable');
        $down = $recentFailures->count() >= $threshold && $recentFailures->every(static fn (bool $reachable): bool => ! $reachable);

        $supplier->forceFill([
            'health' => $result->reachable ? SupplierHealth::Healthy : ($down ? SupplierHealth::Down : SupplierHealth::Degraded),
            'circuit_open_until' => $down ? now()->addMinutes((int) config('suppliers.circuit_open_minutes')) : ($result->reachable ? null : $supplier->circuit_open_until),
        ])->save();

        return $check;
    }
}
