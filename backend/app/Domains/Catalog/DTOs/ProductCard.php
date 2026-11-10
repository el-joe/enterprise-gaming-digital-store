<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Catalog\Models\Product;

final readonly class ProductCard
{
    /**
     * @param  array{amount: string, currency: string, formatted: string}|null  $priceFrom
     */
    public function __construct(
        public Product $product,
        public ?array $priceFrom,
        public bool $priceMayVary,
    ) {}
}
