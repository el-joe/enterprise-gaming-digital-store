<?php

namespace App\Domains\Payments\DTOs;

/**
 * The server-built URLs a provider sends the customer back to after its own UI (M-A6, ADR-039).
 * `sdkReturnUrl` is the value an app SDK must be given on confirm (Stripe PaymentSheet); it is
 * set for `app` attempts only and is published in `next.return_url`.
 */
final readonly class PaymentReturnUrls
{
    public function __construct(
        public string $returnUrl,
        public string $cancelUrl,
        public ?string $sdkReturnUrl = null,
    ) {}
}
