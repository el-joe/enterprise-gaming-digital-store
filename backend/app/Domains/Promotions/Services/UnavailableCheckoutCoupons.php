<?php

namespace App\Domains\Promotions\Services;

use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use App\Domains\Shared\ValueObjects\Money;

/**
 * Fail-closed implementation of the coupon contract (the default binding until M11, which bound
 * DatabaseCheckoutCoupons). Every code is refused, so no discount can be granted without limits
 * being enforced; bind it to switch coupons off.
 */
class UnavailableCheckoutCoupons implements CheckoutCoupons
{
    public const string MESSAGE = 'Coupons are not available yet.';

    public function resolve(CouponLookup $lookup): ResolvedCoupon
    {
        throw new CouponInvalid(self::MESSAGE);
    }

    public function reserve(ResolvedCoupon $coupon, CouponLookup $lookup, int $orderId, Money $discountBase): void
    {
        throw new CouponInvalid(self::MESSAGE);
    }

    /**
     * Nothing can have been reserved through this binding.
     */
    public function release(int $orderId): void {}
}
