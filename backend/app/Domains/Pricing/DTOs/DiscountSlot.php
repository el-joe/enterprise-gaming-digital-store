<?php

namespace App\Domains\Pricing\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * One purchased unit as the cart discount allocator sees it: its price is the weight, and the
 * room is the most discount the margin guard lets it take.
 */
final readonly class DiscountSlot
{
    public function __construct(
        public Money $weight,
        public Money $room,
    ) {}
}
