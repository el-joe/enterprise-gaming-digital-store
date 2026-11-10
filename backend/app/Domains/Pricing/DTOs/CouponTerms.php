<?php

namespace App\Domains\Pricing\DTOs;

use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Shared\ValueObjects\Money;

/**
 * An already validated coupon as the engine needs it. Eligibility and limits belong to M11.
 */
final readonly class CouponTerms
{
    public function __construct(
        public CouponType $type,
        public string $value,
        public ?Money $maxDiscountBase = null,
        public bool $allowBelowMargin = false,
    ) {}
}
