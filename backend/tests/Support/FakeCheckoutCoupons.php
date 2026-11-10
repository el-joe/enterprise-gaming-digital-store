<?php

namespace Tests\Support;

use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Shared\ValueObjects\Money;
use RuntimeException;

/**
 * Test double for the M11 contract. It accepts the codes it was given and records reservations
 * and releases; it enforces no limits, which is exactly what M11 must add.
 */
class FakeCheckoutCoupons implements CheckoutCoupons
{
    /** @var list<array{coupon_id: int, order_id: int, discount_base: string}> */
    public array $reserved = [];

    /** @var list<int> */
    public array $released = [];

    public bool $failOnReserve = false;

    /**
     * @param  array<string, array{coupon: Coupon, terms: CouponTerms, variants?: list<int>}>  $coupons  keyed by code
     */
    public function __construct(private array $coupons = []) {}

    public function resolve(CouponLookup $lookup): ResolvedCoupon
    {
        $entry = $this->coupons[strtoupper($lookup->code)] ?? throw new CouponInvalid;

        return new ResolvedCoupon($entry['coupon']->id, $entry['coupon']->code, $entry['terms'], $entry['variants'] ?? null);
    }

    public function reserve(ResolvedCoupon $coupon, CouponLookup $lookup, int $orderId, Money $discountBase): void
    {
        if ($this->failOnReserve) {
            throw new RuntimeException('Simulated failure late in the placement transaction.');
        }

        $this->reserved[] = ['coupon_id' => $coupon->couponId, 'order_id' => $orderId, 'discount_base' => $discountBase->amount];
    }

    public function release(int $orderId): void
    {
        $this->released[] = $orderId;
    }
}
