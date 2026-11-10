<?php

namespace App\Domains\Orders\DTOs;

use Carbon\CarbonImmutable;

/**
 * A guest order token as returned once to the client. The plain value is never stored or logged.
 */
final readonly class IssuedGuestOrderToken
{
    public function __construct(
        public string $token,
        public string $orderPublicId,
        public CarbonImmutable $expiresAt,
    ) {}

    /**
     * @return array{token: string, order_public_id: string, expires_at: string}
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'order_public_id' => $this->orderPublicId,
            'expires_at' => $this->expiresAt->toIso8601String(),
        ];
    }
}
