<?php

namespace App\Domains\Identity\DTOs;

final readonly class RegistrationData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
