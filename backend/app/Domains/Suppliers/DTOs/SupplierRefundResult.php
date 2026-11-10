<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Shared\ValueObjects\Money;

final readonly class SupplierRefundResult
{
    public function __construct(public bool $refunded, public string $reason, public ?Money $amount = null) {}
}
