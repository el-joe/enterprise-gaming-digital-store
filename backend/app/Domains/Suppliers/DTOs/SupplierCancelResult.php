<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class SupplierCancelResult
{
    public function __construct(public bool $cancelled, public string $reason) {}
}
