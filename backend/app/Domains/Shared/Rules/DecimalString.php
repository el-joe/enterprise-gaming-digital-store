<?php

namespace App\Domains\Shared\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Requires a plain decimal string, so that JSON numbers can never become PHP floats in money code.
 */
class DecimalString implements ValidationRule
{
    public function __construct(
        private int $integerDigits = 14,
        private int $scale = 6,
        private bool $allowZero = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = sprintf('/^\d{1,%d}(\.\d{1,%d})?$/', $this->integerDigits, $this->scale);

        if (! is_string($value) || preg_match($pattern, $value) !== 1) {
            $fail("The {$attribute} field must be a decimal string with at most {$this->scale} decimal places.");

            return;
        }

        if (! $this->allowZero && bccomp($value, '0', $this->scale) !== 1) {
            $fail("The {$attribute} field must be greater than zero.");
        }
    }

    public static function money(): self
    {
        return new self;
    }

    public static function positiveMoney(): self
    {
        return new self(allowZero: false);
    }

    public static function rate(): self
    {
        return new self(12, 12, false);
    }

    public static function percent(): self
    {
        return new self(3, 4);
    }
}
