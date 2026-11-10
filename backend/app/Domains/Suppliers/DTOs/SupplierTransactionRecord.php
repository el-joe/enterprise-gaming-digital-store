<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierOutcomeKind;
use Carbon\CarbonImmutable;

/**
 * One row of a supplier's transaction report, for daily reconciliation.
 */
final readonly class SupplierTransactionRecord
{
    public function __construct(
        public string $externalTransactionId,
        public ?string $clientReference,
        public SupplierOutcomeKind $kind,
        public ?string $supplierStatus,
        public ?Money $cost,
        public ?CarbonImmutable $createdAt,
    ) {}
}
