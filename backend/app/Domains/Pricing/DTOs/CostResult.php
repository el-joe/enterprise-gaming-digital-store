<?php

namespace App\Domains\Pricing\DTOs;

use App\Domains\Shared\ValueObjects\Money;

final readonly class CostResult
{
    public function __construct(
        public ?Money $baseCost,
        public ?string $unavailableReason = null,
        public ?string $costCurrency = null,
    ) {}
}
