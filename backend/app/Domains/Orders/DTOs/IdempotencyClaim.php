<?php

namespace App\Domains\Orders\DTOs;

use Carbon\CarbonImmutable;

/**
 * Either ownership of an idempotency key (with the lock token that fences later writes) or a
 * stored response to replay.
 */
final readonly class IdempotencyClaim
{
    /**
     * @param  array<string, mixed>|null  $replayBody
     */
    private function __construct(
        public int $keyId,
        public ?CarbonImmutable $lockToken,
        public ?int $replayStatus,
        public ?array $replayBody,
    ) {}

    public static function acquired(int $keyId, CarbonImmutable $lockToken): self
    {
        return new self($keyId, $lockToken, null, null);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function replay(int $keyId, int $status, array $body): self
    {
        return new self($keyId, null, $status, $body);
    }

    public function isReplay(): bool
    {
        return $this->replayStatus !== null;
    }
}
