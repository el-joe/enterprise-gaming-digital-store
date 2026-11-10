<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Shared\ValueObjects\Money;

/**
 * A payment as read from the provider by the server. `amount` is what was received or captured
 * in the provider's currency, converted from its own unit; it is compared with the snapshot.
 */
final readonly class VerifiedPaymentState
{
    /**
     * @param  array<string, mixed>  $raw  redacted, non-sensitive fields for the ledger
     */
    public function __construct(
        public ProviderPaymentStatus $status,
        public ?Money $amount = null,
        public ?string $providerCaptureId = null,
        public ?Money $refundedAmount = null,
        public ?Money $fee = null,
        public ?string $cardCountry = null,
        public ?string $cardFingerprint = null,
        public ?string $failureCode = null,
        public ?string $failureMessage = null,
        public array $raw = [],
    ) {}
}
