<?php

namespace App\Domains\Notifications\DTOs;

use App\Domains\Notifications\Enums\PushDeliveryOutcome;

/**
 * What FCM answered for one message (M-A18). `retryAfterSeconds` comes from a `Retry-After`
 * header on a 429 or 5xx answer; `error` is FCM's error code or HTTP status, never the token.
 */
final readonly class PushDeliveryResult
{
    public function __construct(
        public PushDeliveryOutcome $outcome,
        public ?int $retryAfterSeconds = null,
        public ?string $error = null,
    ) {}
}
