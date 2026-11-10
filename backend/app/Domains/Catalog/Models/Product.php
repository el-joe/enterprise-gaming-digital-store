<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Reviews\Models\Review;
use Database\Factories\Catalog\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('products')]
#[Fillable(['gaming_platform_id', 'primary_category_id', 'name', 'slug', 'sku', 'short_description', 'description', 'instructions', 'terms', 'delivery_type', 'delivery_time_minutes', 'max_quantity_per_order', 'is_featured', 'is_trending', 'published_at'])]
#[UseFactory(ProductFactory::class)]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
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
            'delivery_type' => DeliveryType::class,
            'delivery_time_minutes' => 'integer',
            'max_quantity_per_order' => 'integer',
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'sales_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<GamingPlatform, $this>
     */
    public function gamingPlatform(): BelongsTo
    {
        return $this->belongsTo(GamingPlatform::class, 'gaming_platform_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * @return HasMany<ProductCountryRestriction, $this>
     */
    public function productCountryRestrictions(): HasMany
    {
        return $this->hasMany(ProductCountryRestriction::class, 'product_id');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * @return HasMany<ProductAttributeValue, $this>
     */
    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_id');
    }

    /**
     * @return HasMany<ProductDeliveryField, $this>
     */
    public function productDeliveryFields(): HasMany
    {
        return $this->hasMany(ProductDeliveryField::class, 'product_id');
    }

    /**
     * @return HasMany<ProductRelation, $this>
     */
    public function productProductRelations(): HasMany
    {
        return $this->hasMany(ProductRelation::class, 'product_id');
    }

    /**
     * @return HasMany<ProductRelation, $this>
     */
    public function relatedProductProductRelations(): HasMany
    {
        return $this->hasMany(ProductRelation::class, 'related_product_id');
    }

    /**
     * @return HasMany<WishlistItem, $this>
     */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'product_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * @return HasMany<OrderItemFinancial, $this>
     */
    public function orderItemFinancials(): HasMany
    {
        return $this->hasMany(OrderItemFinancial::class, 'product_id');
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    /**
     * @return BelongsToMany<Region, $this>
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'product_regions', 'product_id', 'region_id');
    }

    /**
     * @return BelongsToMany<PaymentGateway, $this>
     */
    public function excludedGateways(): BelongsToMany
    {
        return $this->belongsToMany(PaymentGateway::class, 'payment_gateway_product_exclusions', 'product_id', 'payment_gateway_id');
    }
}
