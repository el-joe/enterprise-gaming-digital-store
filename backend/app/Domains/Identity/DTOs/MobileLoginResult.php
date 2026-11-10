<?php

namespace App\Domains\Identity\DTOs;

use App\Domains\Identity\Models\User;
use Carbon\CarbonImmutable;

/**
 * Either signed in (`user` and `tokens`) or waiting for a two-factor code (`challengeToken`).
 */
final readonly class MobileLoginResult
{
    public function __construct(
        public ?User $user,
        public ?IssuedTokens $tokens,
        public ?string $challengeToken = null,
        public ?CarbonImmutable $challengeExpiresAt = null,
    ) {}

    public function twoFactorRequired(): bool
    {
        return $this->challengeToken !== null;
    }
}
