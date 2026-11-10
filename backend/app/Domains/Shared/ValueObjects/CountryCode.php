<?php

namespace App\Domains\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Validated ISO 3166-1 alpha-2 country code.
 */
final readonly class CountryCode implements Stringable
{
    private function __construct(public string $value) {}

    public static function of(string $value): self
    {
        $value = strtoupper($value);

        if (preg_match('/^[A-Z]{2}$/', $value) !== 1) {
            throw new InvalidArgumentException("Invalid country code [{$value}].");
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
