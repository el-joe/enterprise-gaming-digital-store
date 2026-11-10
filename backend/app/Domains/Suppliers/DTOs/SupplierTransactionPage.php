<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class SupplierTransactionPage
{
    /**
     * @param  list<SupplierTransactionRecord>  $records
     */
    public function __construct(public array $records, public bool $isLast) {}
}
