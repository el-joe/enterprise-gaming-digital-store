<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use Database\Factories\Suppliers\SupplierProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('supplier_products')]
#[Fillable(['supplier_id', 'external_id', 'name', 'brand_name', 'country_code', 'category_name', 'denomination_type', 'currency', 'fixed_denominations', 'min_denomination', 'max_denomination', 'sender_currency', 'cost_map', 'discount_pct', 'fee_fixed', 'fee_pct', 'supports_preorder', 'requires_user_id', 'availability', 'raw', 'last_seen_at', 'removed_at'])]
#[UseFactory(SupplierProductFactory::class)]
class SupplierProduct extends Model
{
    /** @use HasFactory<SupplierProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'denomination_type' => DenominationType::class,
            'fixed_denominations' => 'array',
            'min_denomination' => 'decimal:6',
            'max_denomination' => 'decimal:6',
            'cost_map' => 'array',
            'discount_pct' => 'decimal:4',
            'fee_fixed' => 'decimal:6',
            'fee_pct' => 'decimal:4',
            'supports_preorder' => 'boolean',
            'requires_user_id' => 'boolean',
            'availability' => SupplierAvailability::class,
            'raw' => 'array',
            'last_seen_at' => 'datetime',
            'removed_at' => 'datetime',
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
     * @return HasMany<SupplierProductPrice, $this>
     */
    public function supplierProductPrices(): HasMany
    {
        return $this->hasMany(SupplierProductPrice::class, 'supplier_product_id');
    }

    /**
     * @return HasMany<SupplierProductMapping, $this>
     */
    public function supplierProductMappings(): HasMany
    {
        return $this->hasMany(SupplierProductMapping::class, 'supplier_product_id');
    }
}
