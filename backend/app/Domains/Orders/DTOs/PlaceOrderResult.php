<?php

namespace App\Domains\Orders\DTOs;

/**
 * The HTTP outcome of a placement, stored for idempotent replay. The body holds no secrets.
 */
final readonly class PlaceOrderResult
{
    /**
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        public int $status,
        public array $body,
        public bool $replayed = false,
        public ?string $createdOrderPublicId = null,
    ) {}
}
