<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class SupplierCountryData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $currency,
    ) {}
}
