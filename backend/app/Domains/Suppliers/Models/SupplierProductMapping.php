<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\Currency;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use Database\Factories\Suppliers\SupplierProductMappingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('supplier_product_mappings')]
#[Fillable(['product_variant_id', 'supplier_id', 'supplier_product_id', 'role', 'priority', 'supplier_unit_price', 'supplier_unit_currency', 'override_cost', 'override_cost_currency', 'allowed_countries', 'denied_countries', 'is_active', 'availability', 'last_verified_at'])]
#[UseFactory(SupplierProductMappingFactory::class)]
class SupplierProductMapping extends Model
{
    /** @use HasFactory<SupplierProductMappingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => SupplierProductMappingRole::class,
            'priority' => 'integer',
            'supplier_unit_price' => 'decimal:6',
            'override_cost' => 'decimal:6',
            'allowed_countries' => 'array',
            'denied_countries' => 'array',
            'is_active' => 'boolean',
            'availability' => SupplierAvailability::class,
            'last_verified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo<SupplierProduct, $this>
     */
    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'supplier_product_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function overrideCostCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'override_cost_currency', 'code');
    }

    /**
     * @return HasMany<SupplierTransaction, $this>
     */
    public function supplierTransactions(): HasMany
    {
        return $this->hasMany(SupplierTransaction::class, 'supplier_product_mapping_id');
    }
}
