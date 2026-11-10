<?php

namespace App\Domains\Shared\ValueObjects;

use App\Domains\Shared\Exceptions\CurrencyMismatch;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Immutable money value backed by bcmath strings. Amounts are held at DECIMAL(20,6) precision.
 */
final readonly class Money implements JsonSerializable, Stringable
{
    public const int SCALE = 6;

    private const int WORKING_SCALE = 24;

    private const int MAX_INTEGER_DIGITS = 14;

    private function __construct(public string $amount, public string $currency) {}

    /**
     * Create money from a decimal string or integer. Floats are rejected at runtime because PHP
     * would otherwise coerce them silently into a string union parameter.
     */
    public static function of(mixed $amount, string $currency): self
    {
        return new self(self::normalize($amount), self::normalizeCurrency($currency));
    }

    public static function zero(string $currency): self
    {
        return self::of('0', $currency);
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(self::normalize(bcadd($this->amount, $other->amount, self::SCALE)), $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(self::normalize(bcsub($this->amount, $other->amount, self::SCALE)), $this->currency);
    }

    /**
     * Multiply by a decimal factor, rounding HALF_UP to six places.
     */
    public function multipliedBy(mixed $factor): self
    {
        $product = bcmul($this->amount, self::decimal($factor), self::WORKING_SCALE);

        return new self(self::normalize($product), $this->currency);
    }

    /**
     * Divide by a decimal divisor, rounding HALF_UP to six places.
     */
    public function dividedBy(mixed $divisor): self
    {
        $divisor = self::decimal($divisor);

        if (bccomp($divisor, '0', self::WORKING_SCALE) === 0) {
            throw new InvalidArgumentException('Cannot divide money by zero.');
        }

        return new self(self::normalize(bcdiv($this->amount, $divisor, self::WORKING_SCALE)), $this->currency);
    }

    /**
     * Convert into another currency with an explicit rate. No rate is ever assumed.
     */
    public function convertedTo(string $targetCurrency, mixed $rate): self
    {
        $product = bcmul($this->amount, self::decimal($rate), self::WORKING_SCALE);

        return new self(self::normalize($product), self::normalizeCurrency($targetCurrency));
    }

    /**
     * Round to the given number of minor digits (HALF_UP) for a customer-facing amount.
     */
    public function roundToCurrency(int $exponent): self
    {
        if ($exponent < 0 || $exponent > self::SCALE) {
            throw new InvalidArgumentException("Currency exponent must be between 0 and 6, got {$exponent}.");
        }

        return new self(self::normalize(self::roundHalfUp($this->amount, $exponent)), $this->currency);
    }

    public function negated(): self
    {
        return new self(self::normalize(bcmul($this->amount, '-1', self::SCALE)), $this->currency);
    }

    public function compareTo(self $other): int
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, self::SCALE);
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && bccomp($this->amount, $other->amount, self::SCALE) === 0;
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === -1;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === 1;
    }

    /**
     * @return array{amount: string, currency: string}
     */
    public function jsonSerialize(): array
    {
        return ['amount' => $this->amount, 'currency' => $this->currency];
    }

    public function __toString(): string
    {
        return "{$this->amount} {$this->currency}";
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatch("Cannot combine {$this->currency} with {$other->currency}.");
        }
    }

    private static function decimal(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (! is_string($value) || preg_match('/^[+-]?\d+(\.\d+)?$/', $value) !== 1) {
            throw new InvalidArgumentException('Money values must be decimal strings or integers; floats are forbidden.');
        }

        return ltrim($value, '+');
    }

    private static function normalize(mixed $value): string
    {
        $rounded = self::roundHalfUp(self::decimal($value), self::SCALE);

        if (strlen(ltrim(explode('.', ltrim($rounded, '-'))[0], '0')) > self::MAX_INTEGER_DIGITS) {
            throw new InvalidArgumentException('Money amount exceeds DECIMAL(20,6) range.');
        }

        return bcadd($rounded, '0', self::SCALE);
    }

    private static function normalizeCurrency(string $currency): string
    {
        $currency = strtoupper($currency);

        if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new InvalidArgumentException("Invalid currency code [{$currency}].");
        }

        return $currency;
    }

    /**
     * Round half away from zero; bcmath itself only truncates.
     */
    private static function roundHalfUp(string $value, int $scale): string
    {
        $offset = '0.'.str_repeat('0', $scale).'5';

        $rounded = str_starts_with($value, '-')
            ? bcsub($value, $offset, $scale)
            : bcadd($value, $offset, $scale);

        return bccomp($rounded, '0', $scale) === 0 ? bcadd('0', '0', $scale) : $rounded;
    }
}
