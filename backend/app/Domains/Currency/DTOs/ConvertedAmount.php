<?php

namespace App\Domains\Currency\DTOs;

use App\Domains\Shared\ValueObjects\Money;

final readonly class ConvertedAmount
{
    public function __construct(
        public Money $money,
        public ResolvedRate $rate,
    ) {}
}
