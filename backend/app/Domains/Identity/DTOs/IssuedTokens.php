<?php

namespace App\Domains\Identity\DTOs;

use Carbon\CarbonImmutable;

/**
 * Plain-text tokens returned to the app once. Never logged, never stored in plain text.
 */
final readonly class IssuedTokens
{
    public function __construct(
        public string $accessToken,
        public CarbonImmutable $accessTokenExpiresAt,
        public string $refreshToken,
        public CarbonImmutable $refreshTokenExpiresAt,
    ) {}
}
