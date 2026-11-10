<?php

namespace App\Domains\Payments\DTOs;

/**
 * A webhook exactly as received. `rawBody` is never re-serialized before verification.
 */
final readonly class WebhookRequestData
{
    /**
     * @param  array<string, string>  $headers  lower-case names, first value only
     * @param  array<string, mixed>  $query
     */
    public function __construct(
        public string $rawBody,
        public array $headers,
        public array $query = [],
    ) {}

    public function header(string $name): ?string
    {
        $value = $this->headers[strtolower($name)] ?? null;

        return $value === null || $value === '' ? null : $value;
    }
}
