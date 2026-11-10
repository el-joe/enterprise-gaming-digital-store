<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Shared\ValueObjects\Money;
use Carbon\CarbonImmutable;

final readonly class SupplierBalanceData
{
    public function __construct(
        public Money $balance,
        public CarbonImmutable $capturedAt,
    ) {}
}
