<?php

namespace App\Domains\Mcp\DTOs;

/**
 * A validated write request, ready to be stored as a pending action. `arguments` is the canonical
 * form that is hashed and later executed unchanged.
 */
final readonly class PreparedAction
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(
        public array $arguments,
        public int $riskTier,
        public string $summary,
    ) {}
}
