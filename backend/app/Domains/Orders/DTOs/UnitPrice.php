<?php

namespace App\Domains\Orders\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * One purchased unit after the cart-level discount: base amounts and what the customer pays.
 */
final readonly class UnitPrice
{
    public function __construct(
        public Money $grossBase,
        public Money $discountBase,
        public Money $netBase,
        public Money $netDisplay,
    ) {}
}
