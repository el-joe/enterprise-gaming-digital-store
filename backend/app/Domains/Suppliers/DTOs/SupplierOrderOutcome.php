<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierOutcomeKind;

/**
 * The classified result of createOrder, getOrderStatus or findOrderByReference. `reason` is a
 * short code for timelines and logs; it never carries a supplier body.
 *
 * `vaultCodeId` is set by adapters whose deliverable is already in our vault (internal stock),
 * so no separate fetch is needed.
 */
final readonly class SupplierOrderOutcome
{
    public function __construct(
        public SupplierOutcomeKind $kind,
        public string $reason,
        public ?string $externalTransactionId = null,
        public ?string $supplierErrorCode = null,
        public ?Money $actualCost = null,
        public bool $costReversalPending = false,
        public bool $notSent = false,
        public ?int $vaultCodeId = null,
        public ?int $httpStatus = null,
    ) {}

    public static function fulfilled(string $reason, ?string $externalId, ?Money $cost = null, ?int $vaultCodeId = null, ?int $httpStatus = null): self
    {
        return new self(SupplierOutcomeKind::Fulfilled, $reason, $externalId, actualCost: $cost, vaultCodeId: $vaultCodeId, httpStatus: $httpStatus);
    }

    public static function inProgress(string $reason, ?string $externalId, ?Money $cost = null, ?int $httpStatus = null): self
    {
        return new self(SupplierOutcomeKind::InProgress, $reason, $externalId, actualCost: $cost, httpStatus: $httpStatus);
    }

    public static function rejected(string $reason, ?string $errorCode = null, ?string $externalId = null, bool $costReversalPending = false, bool $notSent = false, ?int $httpStatus = null): self
    {
        return new self(SupplierOutcomeKind::Rejected, $reason, $externalId, $errorCode, costReversalPending: $costReversalPending, notSent: $notSent, httpStatus: $httpStatus);
    }

    public static function ambiguous(string $reason, ?string $errorCode = null, ?string $externalId = null, ?int $httpStatus = null): self
    {
        return new self(SupplierOutcomeKind::Ambiguous, $reason, $externalId, $errorCode, httpStatus: $httpStatus);
    }

    public static function notFound(string $reason = 'not_found'): self
    {
        return new self(SupplierOutcomeKind::NotFound, $reason);
    }

    public function is(SupplierOutcomeKind $kind): bool
    {
        return $this->kind === $kind;
    }
}
