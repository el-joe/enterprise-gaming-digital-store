<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\DTOs\PriceQuote;

final readonly class VariantOffer
{
    /**
     * @param  array{amount: string, currency: string, formatted: string}|null  $price
     */
    public function __construct(
        public ProductVariant $variant,
        public PriceQuote $quote,
        public ?array $price,
    ) {}
}
