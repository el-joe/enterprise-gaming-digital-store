<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\ProviderPaymentStatus;

/**
 * The provider's answer to a create. `next` is what the browser needs to continue on the
 * provider's own UI; it holds publishable values only, never a secret key.
 */
final readonly class CreatePaymentResult
{
    /**
     * @param  array{type: string, client_secret?: string, approve_url?: string, redirect_url?: string, provider_order_id?: string, return_url?: string}  $next
     */
    public function __construct(
        public string $providerPaymentId,
        public ProviderPaymentStatus $status,
        public array $next,
        public ?string $providerOrderId = null,
        public ?string $providerReference = null,
    ) {}
}
