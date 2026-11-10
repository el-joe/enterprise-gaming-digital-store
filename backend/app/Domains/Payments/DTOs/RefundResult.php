<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Shared\ValueObjects\Money;

/**
 * A refund's state as the provider reports it. `Requested` from fetchRefund means the provider
 * has no record of it, so it was never executed.
 */
final readonly class RefundResult
{
    public function __construct(
        public RefundStatus $status,
        public ?string $providerRefundId = null,
        public ?Money $amount = null,
        public ?string $failureReason = null,
    ) {}
}
