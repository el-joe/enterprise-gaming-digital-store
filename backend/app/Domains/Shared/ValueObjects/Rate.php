<?php

namespace App\Domains\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable exchange rate at DECIMAL(24,12) precision: units of the quote currency per one unit
 * of the base currency. Rates are always positive and never floats.
 */
final readonly class Rate
{
    public const int SCALE = 12;

    private const int WORKING_SCALE = 30;

    private function __construct(public string $value) {}

    public static function of(mixed $value): self
    {
        if (! is_string($value) || preg_match('/^\d{1,12}(\.\d+)?$/', $value) !== 1) {
            throw new InvalidArgumentException('Rates must be plain decimal strings; floats are forbidden.');
        }

        $rounded = bcadd($value, '0.'.str_repeat('0', self::SCALE).'5', self::SCALE);

        if (bccomp($rounded, '0', self::SCALE) !== 1) {
            throw new InvalidArgumentException('Rates must be greater than zero.');
        }

        return new self($rounded);
    }

    /**
     * Apply a percentage markup: rate * (1 + markup / 100).
     */
    public function withMarkup(Percentage $markup): self
    {
        $factor = bcadd('1', bcdiv($markup->value, '100', self::WORKING_SCALE), self::WORKING_SCALE);

        return self::of(bcmul($this->value, $factor, self::WORKING_SCALE));
    }

    /**
     * Absolute percentage change from a previous rate.
     */
    public function changePercentFrom(self $previous): string
    {
        $difference = bcsub($this->value, $previous->value, self::SCALE);

        if (str_starts_with($difference, '-')) {
            $difference = substr($difference, 1);
        }

        return bcmul(bcdiv($difference, $previous->value, self::WORKING_SCALE), '100', self::SCALE);
    }

    public function equals(self $other): bool
    {
        return bccomp($this->value, $other->value, self::SCALE) === 0;
    }
}
