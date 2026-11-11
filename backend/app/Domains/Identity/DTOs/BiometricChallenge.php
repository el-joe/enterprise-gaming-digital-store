<?php

namespace App\Domains\Identity\DTOs;

use Carbon\CarbonImmutable;

/**
 * A biometric challenge to sign on the device (OD-1, ADR-040). The device signs the exact UTF-8
 * bytes of `challenge` with the registered private key (ECDSA P-256, SHA-256, DER signature).
 */
final readonly class BiometricChallenge
{
    public function __construct(
        public string $challenge,
        public CarbonImmutable $expiresAt,
    ) {}

    /**
     * @return array{challenge: string, algorithm: string, expires_at: string}
     */
    public function toArray(): array
    {
        return ['challenge' => $this->challenge, 'algorithm' => 'ES256', 'expires_at' => $this->expiresAt->toIso8601String()];
    }
}
