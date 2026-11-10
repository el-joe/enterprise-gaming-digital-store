<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Enums\SupplierTransactionCodeRetrievalStatus;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use Database\Factories\Suppliers\SupplierTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('supplier_transactions')]
#[Fillable(['order_item_unit_id', 'supplier_id', 'supplier_product_mapping_id', 'attempt_no', 'client_reference', 'external_transaction_id', 'outcome_reason', 'supplier_error_code', 'is_fallback', 'requested_currency', 'actual_cost_currency', 'cost_reversal_pending', 'submitted_at', 'next_verification_at', 'verification_attempts', 'verification_deadline_at', 'resolution_note'])]
#[UseFactory(SupplierTransactionFactory::class)]
class SupplierTransaction extends Model
{
    /** @use HasFactory<SupplierTransactionFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the columns that receive a ULID on creation.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_no' => 'integer',
            'status' => SupplierTransactionStatus::class,
            'is_fallback' => 'boolean',
            'code_retrieval_status' => SupplierTransactionCodeRetrievalStatus::class,
            'requested_amount' => 'decimal:6',
            'actual_cost' => 'decimal:6',
            'cost_reversal_pending' => 'boolean',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'next_verification_at' => 'datetime',
            'verification_attempts' => 'integer',
            'verification_deadline_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OrderItemUnit, $this>
     */
    public function orderItemUnit(): BelongsTo
    {
        return $this->belongsTo(OrderItemUnit::class, 'order_item_unit_id');
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo<SupplierProductMapping, $this>
     */
    public function supplierProductMapping(): BelongsTo
    {
        return $this->belongsTo(SupplierProductMapping::class, 'supplier_product_mapping_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * @return HasMany<SupplierApiLog, $this>
     */
    public function supplierApiLogs(): HasMany
    {
        return $this->hasMany(SupplierApiLog::class, 'supplier_transaction_id');
    }

    /**
     * @return HasMany<DigitalCode, $this>
     */
    public function digitalCodes(): HasMany
    {
        return $this->hasMany(DigitalCode::class, 'supplier_transaction_id');
    }
}
