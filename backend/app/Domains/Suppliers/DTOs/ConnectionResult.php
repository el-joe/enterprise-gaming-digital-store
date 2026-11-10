<?php

namespace App\Domains\Suppliers\DTOs;

final readonly class ConnectionResult
{
    public function __construct(
        public bool $reachable,
        public ?int $latencyMs = null,
        public ?string $error = null,
    ) {}
}
