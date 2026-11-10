<?php

namespace App\Domains\Pricing\DTOs;

/**
 * The buyer-side inputs to a price. Null currency means the base currency. With forCheckout a
 * stale rate blocks the quote; display tolerates one and flags it. `paymentCurrency` is the
 * currency the gateway will charge when it is known and differs from the display currency: the
 * margin floor then includes the conversion cost of charging in it.
 */
final readonly class PricingContext
{
    public function __construct(
        public ?string $country = null,
        public ?string $currency = null,
        public ?CouponTerms $coupon = null,
        public bool $forCheckout = false,
        public ?string $paymentCurrency = null,
    ) {}
}
