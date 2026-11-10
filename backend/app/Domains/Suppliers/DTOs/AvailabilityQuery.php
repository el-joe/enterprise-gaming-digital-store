<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class AvailabilityQuery
{
    public function __construct(
        public string $supplierProductId,
        public ?int $productVariantId = null,
        public ?string $denomination = null,
    ) {}
}
