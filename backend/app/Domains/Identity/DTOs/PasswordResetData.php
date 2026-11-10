<?php

namespace App\Domains\Identity\DTOs;

final readonly class PasswordResetData
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
    ) {}
}
