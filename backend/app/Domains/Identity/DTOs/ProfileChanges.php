<?php

namespace App\Domains\Identity\DTOs;

/**
 * A validated partial profile update (M-A5, PATCH semantics): only the keys present are changed,
 * and a present null clears an optional field. Keys: `name`, `locale`, `display_currency`,
 * `country_code`, `phone`.
 */
final readonly class ProfileChanges
{
    /**
     * @param  array<string, string|null>  $attributes
     */
    public function __construct(public array $attributes) {}
}
