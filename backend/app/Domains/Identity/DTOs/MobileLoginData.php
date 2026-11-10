<?php

namespace App\Domains\Identity\DTOs;

final readonly class MobileLoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
        public ?string $ip,
    ) {}

    /**
     * The credentials in the shape the shared web login check takes.
     */
    public function credentials(): LoginData
    {
        return new LoginData($this->email, $this->password, false, $this->ip);
    }
}
