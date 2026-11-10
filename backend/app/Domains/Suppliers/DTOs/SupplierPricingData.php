<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class SupplierPricingData
{
    /**
     * @param  list<SupplierPriceData>  $prices
     */
    public function __construct(
        public SupplierProductData $product,
        public array $prices,
    ) {}
}
