<?php

namespace App\Domains\Payments\DTOs;

final readonly class VerifiedWebhook
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $eventId,
        public string $eventType,
        public array $payload,
    ) {}
}
