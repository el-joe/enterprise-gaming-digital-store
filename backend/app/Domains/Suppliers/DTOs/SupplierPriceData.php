<?php

namespace App\Domains\Suppliers\DTOs;

/**
 * The effective cost of one denomination: what the supplier charges us, in its sender currency.
 */
final readonly class SupplierPriceData
{
    public function __construct(
        public string $denomination,
        public string $cost,
        public string $costCurrency,
    ) {}
}
