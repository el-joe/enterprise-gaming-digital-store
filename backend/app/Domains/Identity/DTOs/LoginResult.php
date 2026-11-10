<?php

namespace App\Domains\Identity\DTOs;

use App\Domains\Identity\Models\User;

final readonly class LoginResult
{
    public function __construct(
        public ?User $user,
        public bool $twoFactorRequired,
        public bool $twoFactorEnrollmentRequired,
    ) {}
}
