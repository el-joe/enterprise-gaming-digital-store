<?php

namespace App\Domains\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable percentage at DECIMAL(7,4) precision, for example "12.5000".
 */
final readonly class Percentage
{
    private function __construct(public string $value) {}

    public static function of(mixed $value): self
    {
        if (is_int($value)) {
            $value = (string) $value;
        }

        if (! is_string($value) || preg_match('/^[+-]?\d{1,3}(\.\d{1,4})?$/', $value) !== 1) {
            throw new InvalidArgumentException('Percentage must be a decimal string with at most four places.');
        }

        return new self(bcadd(ltrim($value, '+'), '0', 4));
    }

    public function applyTo(Money $money): Money
    {
        return $money->multipliedBy($this->value)->dividedBy('100');
    }
}
