<?php

namespace App\Domains\Promotions\DTOs;

use App\Domains\Pricing\DTOs\CouponTerms;

/**
 * A coupon that passed validation for a cart. `eligibleVariantIds` null means every line.
 */
final readonly class ResolvedCoupon
{
    /**
     * @param  list<int>|null  $eligibleVariantIds
     */
    public function __construct(
        public int $couponId,
        public string $code,
        public CouponTerms $terms,
        public ?array $eligibleVariantIds = null,
    ) {}

    public function appliesTo(int $productVariantId): bool
    {
        return $this->eligibleVariantIds === null || in_array($productVariantId, $this->eligibleVariantIds, true);
    }
}
