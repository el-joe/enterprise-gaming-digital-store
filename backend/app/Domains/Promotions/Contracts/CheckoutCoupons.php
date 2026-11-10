<?php

namespace App\Domains\Promotions\Contracts;

use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use App\Domains\Shared\ValueObjects\Money;

/**
 * The only way cart and checkout code reaches coupons. M11 implements validity, eligibility and
 * usage limits behind it; until then the container binds UnavailableCheckoutCoupons, which
 * refuses every code. Implementations must not make HTTP calls: reserve() and release() run
 * inside the order transaction.
 */
interface CheckoutCoupons
{
    /**
     * Validate a code against this cart and return the terms the pricing engine needs.
     * A fixed-value coupon's value must already be expressed in the base currency.
     *
     * @throws CouponInvalid
     */
    public function resolve(CouponLookup $lookup): ResolvedCoupon;

    /**
     * Reserve one use for an order being placed. Called inside the placement transaction; the
     * implementation locks the coupon row and enforces its limits, so a refusal rolls the
     * whole placement back.
     *
     * @throws CouponInvalid
     */
    public function reserve(ResolvedCoupon $coupon, CouponLookup $lookup, int $orderId, Money $discountBase): void;

    /**
     * Release the reservation of an order that was cancelled or failed before payment. Called
     * inside the transition transaction. Must be idempotent.
     */
    public function release(int $orderId): void;
}
