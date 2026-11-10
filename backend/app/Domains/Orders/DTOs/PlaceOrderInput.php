<?php

namespace App\Domains\Orders\DTOs;

/**
 * A validated place-order request. `requestHash` fingerprints the body for idempotency.
 */
final readonly class PlaceOrderInput
{
    public function __construct(
        public CartOwner $owner,
        public string $idempotencyKey,
        public string $requestHash,
        public string $displayCurrency,
        public string $confirmedCurrency,
        public ?string $email,
        public string $country,
        public string $gateway,
        public ?string $method,
        public string $expectedTotal,
        public bool $marketingConsent,
        public ?string $gaClientId,
        public ?string $ip,
        public ?string $userAgent,
        public string $locale,
    ) {}
}
