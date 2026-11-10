<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Models\PaymentGateway;

/**
 * The outcome for one gateway: eligible with a payment currency, or refused with a reason
 * code that is safe to show (no thresholds or rule internals).
 */
final readonly class GatewayEligibility
{
    public function __construct(
        public PaymentGateway $gateway,
        public ?string $paymentCurrency,
        public ?string $refusal = null,
    ) {}

    public function isEligible(): bool
    {
        return $this->paymentCurrency !== null && $this->refusal === null;
    }
}
