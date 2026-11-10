<?php

namespace App\Domains\Promotions\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * What a coupon is validated against: who is buying and what is in the cart, before any coupon.
 */
final readonly class CouponLookup
{
    /**
     * @param  list<int>  $productVariantIds
     * @param  list<int>  $productIds
     */
    public function __construct(
        public string $code,
        public ?int $userId,
        public ?string $email,
        public Money $subtotalBase,
        public array $productVariantIds,
        public array $productIds,
    ) {}
}
