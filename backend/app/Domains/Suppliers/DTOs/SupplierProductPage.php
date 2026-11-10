<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class SupplierProductPage
{
    /**
     * @param  list<SupplierProductData>  $products
     */
    public function __construct(
        public array $products,
        public int $page,
        public bool $isLast,
    ) {}
}
