<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Suppliers\SupplierApiLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_api_logs')]
#[Fillable(['supplier_id', 'supplier_transaction_id', 'operation', 'method', 'url', 'http_status', 'duration_ms', 'succeeded', 'error_class', 'request_redacted', 'response_redacted'])]
#[UseFactory(SupplierApiLogFactory::class)]
class SupplierApiLog extends Model
{
    /** @use HasFactory<SupplierApiLogFactory> */
    use AppendOnly, HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'duration_ms' => 'integer',
            'succeeded' => 'boolean',
            'request_redacted' => 'array',
            'response_redacted' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo<SupplierTransaction, $this>
     */
    public function supplierTransaction(): BelongsTo
    {
        return $this->belongsTo(SupplierTransaction::class, 'supplier_transaction_id');
    }
}
