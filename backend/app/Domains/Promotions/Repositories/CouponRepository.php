<?php

namespace App\Domains\Promotions\Repositories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Orders\Models\Order;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CouponRepository
{
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::query()->where('code', $code)->first();
    }

    /**
     * Lock the coupon row; every reservation of one coupon is serialised behind it.
     */
    public function lock(int $couponId): ?Coupon
    {
        return Coupon::query()->whereKey($couponId)->lockForUpdate()->first();
    }

    /**
     * Uses held by this buyer: reserved or redeemed, matched by account or by email (idx_cr_user).
     */
    public function activeUsesBy(int $couponId, ?int $userId, ?string $emailHash): int
    {
        if ($userId === null && $emailHash === null) {
            return 0;
        }

        return CouponRedemption::query()
            ->where('coupon_id', $couponId)
            ->whereIn('status', [CouponRedemptionStatus::Reserved->value, CouponRedemptionStatus::Redeemed->value])
            ->where(static function (Builder $query) use ($userId, $emailHash): void {
                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }

                if ($emailHash !== null) {
                    $query->orWhere('email_hash', $emailHash);
                }
            })
            ->count();
    }

    /**
     * Whether this buyer already has a paid order (for first-order-only coupons).
     */
    public function hasPaidOrder(?int $userId, ?string $email): bool
    {
        if ($userId === null && $email === null) {
            return false;
        }

        return Order::query()->whereNotNull('paid_at')
            ->where(static function (Builder $query) use ($userId, $email): void {
                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }

                if ($email !== null) {
                    $query->orWhere('contact_email', $email);
                }
            })
            ->exists();
    }

    /**
     * The cart's variants a coupon restricted by product, category or platform applies to.
     *
     * @param  array{products?: list<int>, categories?: list<int>, platforms?: list<int>}  $applicableTo
     * @param  list<int>  $variantIds
     * @return list<int>
     */
    public function eligibleVariantIds(array $applicableTo, array $variantIds): array
    {
        $products = array_map('intval', $applicableTo['products'] ?? []);
        $categories = array_map('intval', $applicableTo['categories'] ?? []);
        $platforms = array_map('intval', $applicableTo['platforms'] ?? []);

        $matching = Product::query()->select('products.id')->where(static function (Builder $query) use ($products, $categories, $platforms): void {
            if ($products !== []) {
                $query->orWhereIn('products.id', $products);
            }

            if ($platforms !== []) {
                $query->orWhereIn('gaming_platform_id', $platforms);
            }

            if ($categories !== []) {
                $query->orWhereIn('primary_category_id', $categories)
                    ->orWhereHas('categories', static fn (Builder $category) => $category->whereIn('categories.id', $categories));
            }
        });

        return ProductVariant::query()->whereIn('id', $variantIds === [] ? [0] : $variantIds)->whereIn('product_id', $matching)
            ->orderBy('id')->pluck('id')->map(static fn ($id): int => (int) $id)->all();
    }

    /**
     * @return Collection<int, CouponRedemption>
     */
    public function reservedFor(int $orderId): Collection
    {
        return CouponRedemption::query()->where('order_id', $orderId)->where('status', CouponRedemptionStatus::Reserved->value)->get();
    }

    public function lockRedemption(int $redemptionId): ?CouponRedemption
    {
        return CouponRedemption::query()->whereKey($redemptionId)->lockForUpdate()->first();
    }
}
