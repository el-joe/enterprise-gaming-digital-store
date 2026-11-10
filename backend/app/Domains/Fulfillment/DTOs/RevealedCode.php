<?php

namespace App\Domains\Fulfillment\DTOs;

use LogicException;
use SensitiveParameter;

/**
 * Decrypted code material on its way to the authorised viewer. Never serialized, never dumped.
 */
final readonly class RevealedCode
{
    public function __construct(
        #[SensitiveParameter] public string $code,
        #[SensitiveParameter] public ?string $pin,
        #[SensitiveParameter] public ?string $redemptionUrl,
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
