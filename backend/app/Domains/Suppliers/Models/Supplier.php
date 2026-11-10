<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\Enums\SupplierHealth;
use App\Domains\Suppliers\Enums\SupplierStatus;
use Database\Factories\Suppliers\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('suppliers')]
#[Fillable(['name', 'adapter_key', 'mode', 'base_url_override', 'priority', 'fallback_enabled', 'fallback_supplier_id', 'max_fallback_attempts', 'connect_timeout_ms', 'request_timeout_ms', 'retry_policy', 'verification_deadline_minutes', 'per_request_cap_base', 'low_balance_threshold', 'balance_currency', 'last_balance', 'last_balance_at', 'health', 'circuit_open_until'])]
#[UseFactory(SupplierFactory::class)]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SupplierStatus::class,
            'mode' => EnvironmentMode::class,
            'priority' => 'integer',
            'fallback_enabled' => 'boolean',
            'max_fallback_attempts' => 'integer',
            'connect_timeout_ms' => 'integer',
            'request_timeout_ms' => 'integer',
            'retry_policy' => 'array',
            'verification_deadline_minutes' => 'integer',
            'per_request_cap_base' => 'decimal:6',
            'low_balance_threshold' => 'decimal:6',
            'last_balance' => 'decimal:6',
            'last_balance_at' => 'datetime',
            'health' => SupplierHealth::class,
            'circuit_open_until' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function fallbackSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'fallback_supplier_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function balanceCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'balance_currency', 'code');
    }

    /**
     * @return HasMany<Supplier, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Supplier::class, 'fallback_supplier_id');
    }

    /**
     * @return HasMany<SupplierCredential, $this>
     */
    public function supplierCredentials(): HasMany
    {
        return $this->hasMany(SupplierCredential::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierProduct, $this>
     */
    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierProductMapping, $this>
     */
    public function supplierProductMappings(): HasMany
    {
        return $this->hasMany(SupplierProductMapping::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierTransaction, $this>
     */
    public function supplierTransactions(): HasMany
    {
        return $this->hasMany(SupplierTransaction::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierApiLog, $this>
     */
    public function supplierApiLogs(): HasMany
    {
        return $this->hasMany(SupplierApiLog::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierSyncRun, $this>
     */
    public function supplierSyncRuns(): HasMany
    {
        return $this->hasMany(SupplierSyncRun::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierBalanceSnapshot, $this>
     */
    public function supplierBalanceSnapshots(): HasMany
    {
        return $this->hasMany(SupplierBalanceSnapshot::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierHealthCheck, $this>
     */
    public function supplierHealthChecks(): HasMany
    {
        return $this->hasMany(SupplierHealthCheck::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierWebhook, $this>
     */
    public function supplierWebhooks(): HasMany
    {
        return $this->hasMany(SupplierWebhook::class, 'supplier_id');
    }

    /**
     * @return HasMany<OrderItemFinancial, $this>
     */
    public function orderItemFinancials(): HasMany
    {
        return $this->hasMany(OrderItemFinancial::class, 'supplier_id');
    }
}
