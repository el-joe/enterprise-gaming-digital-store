<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Suppliers\SupplierHealthCheckFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_health_checks')]
#[Fillable(['supplier_id', 'reachable', 'latency_ms', 'error'])]
#[UseFactory(SupplierHealthCheckFactory::class)]
class SupplierHealthCheck extends Model
{
    /** @use HasFactory<SupplierHealthCheckFactory> */
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
            'reachable' => 'boolean',
            'latency_ms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
