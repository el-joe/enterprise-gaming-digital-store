<?php

namespace App\Domains\Identity\DTOs;

final readonly class TwoFactorEnrollment
{
    public function __construct(
        public string $secret,
        public string $otpauthUri,
    ) {}
}
