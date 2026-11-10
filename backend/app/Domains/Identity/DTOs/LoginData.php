<?php

namespace App\Domains\Identity\DTOs;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
        public ?string $ip,
    ) {}
}
