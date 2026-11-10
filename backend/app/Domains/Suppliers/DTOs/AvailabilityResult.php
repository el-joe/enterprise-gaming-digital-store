<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Suppliers\Enums\SupplierAvailability;

final readonly class AvailabilityResult
{
    public function __construct(
        public SupplierAvailability $availability,
        public bool $advisory,
    ) {}
}
