<?php

namespace App\Domains\Payments\DTOs;

final readonly class ResolvedPaymentCurrency
{
    public function __construct(
        public int $paymentGatewayId,
        public string $currency,
    ) {}
}
