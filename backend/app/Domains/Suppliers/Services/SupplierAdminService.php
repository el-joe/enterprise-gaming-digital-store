<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierApiLog;
use App\Domains\Suppliers\Models\SupplierBalanceSnapshot;
use App\Domains\Suppliers\Models\SupplierCredential;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Supplier records (spec Phase 5, admin supplier management). Credentials are write-only: they
 * are stored encrypted, audited without their values and never returned.
 *
 * @extends CrudService<Supplier>
 */
class SupplierAdminService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private SupplierAdapterRegistry $registry)
    {
        parent::__construct($lists, $audit);
    }

    /**
     * @param  array<string, string>  $credentials
     */
    public function setCredentials(Supplier $supplier, EnvironmentMode $mode, array $credentials, ?string $webhookSecret): SupplierCredential
    {
        return DB::transaction(function () use ($supplier, $mode, $credentials, $webhookSecret): SupplierCredential {
            $row = SupplierCredential::query()->where('supplier_id', $supplier->id)->where('mode', $mode)->lockForUpdate()->first()
                ?? (new SupplierCredential)->forceFill(['supplier_id' => $supplier->id, 'mode' => $mode]);

            $row->forceFill(['credentials' => $credentials, 'webhook_secret' => $webhookSecret, 'rotated_at' => now()])->save();

            $this->audit->record(new AuditEntry('suppliers.credentials_set', $supplier, after: ['mode' => $mode->value, 'keys' => array_keys($credentials)]));

            return $row;
        });
    }

    /**
     * @return array<string, bool> configured state per mode
     */
    public function credentialState(Supplier $supplier): array
    {
        $modes = SupplierCredential::query()->where('supplier_id', $supplier->id)->pluck('mode')->map(static fn (EnvironmentMode|string $mode): string => $mode instanceof EnvironmentMode ? $mode->value : $mode)->all();

        return array_combine(
            array_map(static fn (EnvironmentMode $mode): string => $mode->value, EnvironmentMode::cases()),
            array_map(static fn (EnvironmentMode $mode): bool => in_array($mode->value, $modes, true), EnvironmentMode::cases()),
        );
    }

    public function logs(Supplier $supplier, ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            SupplierApiLog::query()->where('supplier_id', $supplier->id),
            $list,
            ['succeeded' => 'bool', 'operation' => 'exact', 'supplier_transaction_id' => 'exact'],
            ['id', 'created_at', 'duration_ms'],
        );
    }

    /**
     * @return array{balance: array{amount: string, currency: string, at: ?string}|null, history: list<array{amount: string, currency: string, at: string}>}
     */
    public function balance(Supplier $supplier): array
    {
        return [
            'balance' => $supplier->last_balance === null ? null : ['amount' => $supplier->last_balance, 'currency' => (string) $supplier->balance_currency, 'at' => $supplier->last_balance_at?->toIso8601String()],
            'history' => SupplierBalanceSnapshot::query()->where('supplier_id', $supplier->id)->latest('captured_at')->limit(30)->get()
                ->map(static fn (SupplierBalanceSnapshot $row): array => ['amount' => $row->balance, 'currency' => $row->currency, 'at' => $row->captured_at->toIso8601String()])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function capabilities(Supplier $supplier): array
    {
        return $this->registry->for($supplier)->capabilities()->toArray();
    }

    /**
     * @return array{reachable: bool, latency_ms: ?int, error: ?string}
     */
    public function testConnection(Supplier $supplier): array
    {
        $result = $this->registry->for($supplier)->testConnection();
        $this->audit->record(new AuditEntry('suppliers.connection_tested', $supplier, after: ['reachable' => $result->reachable]));

        return ['reachable' => $result->reachable, 'latency_ms' => $result->latencyMs, 'error' => $result->error];
    }

    protected function model(): string
    {
        return Supplier::class;
    }

    protected function auditPrefix(): string
    {
        return 'suppliers.supplier';
    }

    protected function filters(): array
    {
        return ['status' => 'exact', 'adapter_key' => 'exact', 'health' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'name', 'priority', 'created_at'];
    }

    protected function searchColumns(): array
    {
        return ['name'];
    }

    /**
     * @param  Supplier  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $key = $attributes['adapter_key'] ?? $model->adapter_key;

        if (! $this->registry->has((string) $key)) {
            throw ValidationException::withMessages(['adapter_key' => ['No adapter is registered for this key.']]);
        }

        $override = $attributes['base_url_override'] ?? null;

        if (is_string($override) && $override !== '') {
            $parts = parse_url($override);

            if (($parts['scheme'] ?? null) !== 'https' || ! in_array(strtolower((string) ($parts['host'] ?? '')), config("suppliers.override_hosts.{$key}", []), true)) {
                throw ValidationException::withMessages(['base_url_override' => ['The override must be HTTPS and an allowed host for this adapter.']]);
            }
        }

        if (isset($attributes['fallback_supplier_id']) && $model->exists && (int) $attributes['fallback_supplier_id'] === $model->id) {
            throw ValidationException::withMessages(['fallback_supplier_id' => ['A supplier cannot fall back to itself.']]);
        }

        // Switching sandbox/production: the target mode must already have credentials, so a live
        // supplier is never left calling a provider without keys (same rule as payment gateways).
        if ($model->exists && isset($attributes['mode']) && $attributes['mode'] !== $model->mode?->value
            && ! SupplierCredential::query()->where('supplier_id', $model->id)->where('mode', EnvironmentMode::from($attributes['mode']))->exists()) {
            throw ValidationException::withMessages(['mode' => [__('admin.suppliers.mode_needs_credentials')]]);
        }

        return $attributes;
    }

    /**
     * @param  Supplier  $model
     */
    protected function applyGuarded(Model $model, array $data): void
    {
        if (isset($data['status'])) {
            $model->forceFill(['status' => SupplierStatus::from($data['status'])]);
        } elseif (! $model->exists) {
            $model->forceFill(['status' => SupplierStatus::Inactive]);
        }
    }

    /**
     * @param  Supplier  $model
     */
    protected function guardDelete(Model $model): void
    {
        if ($model->supplierTransactions()->exists()) {
            throw new ApplicationException('A supplier with transactions cannot be deleted; deactivate it instead.', 'conflict', 409);
        }
    }
}
