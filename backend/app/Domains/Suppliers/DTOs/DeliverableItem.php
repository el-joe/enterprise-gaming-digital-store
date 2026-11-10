<?php

namespace App\Domains\Suppliers\DTOs;

use LogicException;
use SensitiveParameter;

/**
 * One delivered code. Held in memory only between the supplier response and the vault: it
 * refuses serialization (so it can never enter a queue payload or cache) and hides its values
 * from dumps and exception traces.
 */
final readonly class DeliverableItem
{
    public function __construct(
        #[SensitiveParameter] public string $code,
        #[SensitiveParameter] public ?string $pin = null,
        #[SensitiveParameter] public ?string $redemptionUrl = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['code' => '[REDACTED]', 'pin' => '[REDACTED]', 'redemption_url' => '[REDACTED]'];
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw new LogicException('Digital code material cannot be serialized.');
    }
}
