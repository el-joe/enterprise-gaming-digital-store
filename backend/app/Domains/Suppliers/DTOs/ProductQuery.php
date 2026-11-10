<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class ProductQuery
{
    public function __construct(
        public int $page = 1,
        public int $size = 100,
        public ?string $countryCode = null,
    ) {}
}
