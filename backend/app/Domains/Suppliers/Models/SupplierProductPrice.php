<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Suppliers\SupplierProductPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_product_prices')]
#[Fillable(['supplier_product_id', 'denomination', 'cost', 'cost_currency', 'captured_at'])]
#[UseFactory(SupplierProductPriceFactory::class)]
class SupplierProductPrice extends Model
{
    /** @use HasFactory<SupplierProductPriceFactory> */
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
            'denomination' => 'decimal:6',
            'cost' => 'decimal:6',
            'captured_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SupplierProduct, $this>
     */
    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'supplier_product_id');
    }
}
