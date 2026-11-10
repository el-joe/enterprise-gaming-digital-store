<?php

namespace App\Domains\Promotions\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\Order;
use App\Domains\Promotions\Enums\CouponCreatedByType;
use App\Domains\Promotions\Enums\CouponType;
use Database\Factories\Promotions\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('coupons')]
#[Fillable(['code', 'type', 'value', 'currency', 'max_discount_base', 'min_order_base', 'usage_limit', 'usage_limit_per_user', 'applicable_to', 'stackable', 'allow_below_margin', 'first_order_only', 'starts_at', 'ends_at', 'is_active', 'created_by_type'])]
#[UseFactory(CouponFactory::class)]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'decimal:6',
            'max_discount_base' => 'decimal:6',
            'min_order_base' => 'decimal:6',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'used_count' => 'integer',
            'applicable_to' => 'array',
            'stackable' => 'boolean',
            'allow_below_margin' => 'boolean',
            'first_order_only' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'created_by_type' => CouponCreatedByType::class,
        ];
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    /**
     * @return HasMany<Cart, $this>
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'coupon_id');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id');
    }

    /**
     * @return HasMany<CouponRedemption, $this>
     */
    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class, 'coupon_id');
    }
}
