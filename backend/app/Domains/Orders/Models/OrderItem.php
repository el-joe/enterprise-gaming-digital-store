<?php

namespace App\Domains\Orders\Models;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Reviews\Models\Review;
use Database\Factories\Orders\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('order_items')]
#[Fillable(['order_id', 'product_id', 'product_variant_id', 'name_snapshot', 'sku_snapshot', 'delivery_type', 'quantity', 'pricing_breakdown'])]
#[UseFactory(OrderItemFactory::class)]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_type' => DeliveryType::class,
            'quantity' => 'integer',
            'unit_price_base' => 'decimal:6',
            'compare_at_base' => 'decimal:6',
            'discount_base' => 'decimal:6',
            'line_total_base' => 'decimal:6',
            'estimated_unit_cost_base' => 'decimal:6',
            'custom_amount' => 'decimal:6',
            'pricing_breakdown' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * @return HasMany<OrderItemDeliveryValue, $this>
     */
    public function orderItemDeliveryValues(): HasMany
    {
        return $this->hasMany(OrderItemDeliveryValue::class, 'order_item_id');
    }

    /**
     * @return HasMany<OrderItemUnit, $this>
     */
    public function orderItemUnits(): HasMany
    {
        return $this->hasMany(OrderItemUnit::class, 'order_item_id');
    }

    /**
     * @return HasOne<OrderItemFinancial, $this>
     */
    public function orderItemFinancial(): HasOne
    {
        return $this->hasOne(OrderItemFinancial::class, 'order_item_id');
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'order_item_id');
    }
}
