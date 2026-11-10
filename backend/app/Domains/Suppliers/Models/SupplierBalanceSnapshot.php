<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Suppliers\SupplierBalanceSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_balance_snapshots')]
#[Fillable(['supplier_id', 'currency', 'captured_at'])]
#[UseFactory(SupplierBalanceSnapshotFactory::class)]
class SupplierBalanceSnapshot extends Model
{
    /** @use HasFactory<SupplierBalanceSnapshotFactory> */
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
            'balance' => 'decimal:6',
            'captured_at' => 'datetime',
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
