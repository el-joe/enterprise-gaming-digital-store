<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Currency\Models\Currency;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Pricing\Models\PricingOverrideLog;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Database\Factories\Catalog\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('product_variants')]
#[Fillable(['product_id', 'region_id', 'sku', 'title', 'denomination_value', 'denomination_currency', 'is_custom_amount', 'custom_min', 'custom_max', 'manual_cost_base', 'compare_at_base', 'availability', 'is_active', 'sort_order'])]
#[UseFactory(ProductVariantFactory::class)]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory, HasUlids, SoftDeletes;

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
            'denomination_value' => 'decimal:6',
            'is_custom_amount' => 'boolean',
            'custom_min' => 'decimal:6',
            'custom_max' => 'decimal:6',
            'manual_cost_base' => 'decimal:6',
            'price_base' => 'decimal:6',
            'compare_at_base' => 'decimal:6',
            'availability' => ProductVariantAvailability::class,
            'pricing_dirty_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function denominationCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'denomination_currency', 'code');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    /**
     * @return HasMany<SupplierProductMapping, $this>
     */
    public function supplierProductMappings(): HasMany
    {
        return $this->hasMany(SupplierProductMapping::class, 'product_variant_id');
    }

    /**
     * @return HasMany<VariantPrice, $this>
     */
    public function variantPrices(): HasMany
    {
        return $this->hasMany(VariantPrice::class, 'product_variant_id');
    }

    /**
     * @return HasMany<PricingOverrideLog, $this>
     */
    public function pricingOverridesLog(): HasMany
    {
        return $this->hasMany(PricingOverrideLog::class, 'product_variant_id');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_variant_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }

    /**
     * @return HasMany<DigitalCode, $this>
     */
    public function digitalCodes(): HasMany
    {
        return $this->hasMany(DigitalCode::class, 'product_variant_id');
    }
}
