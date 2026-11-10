<?php

namespace App\Domains\Pricing\DTOs;

/**
 * The buyer-side inputs to a price. Null currency means the base currency. With forCheckout a
 * stale rate blocks the quote; display tolerates one and flags it.
 */
final readonly class PricingContext
{
    public function __construct(
        public ?string $country = null,
        public ?string $currency = null,
        public ?CouponTerms $coupon = null,
        public bool $forCheckout = false,
    ) {}
}
