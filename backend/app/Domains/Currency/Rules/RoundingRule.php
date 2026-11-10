<?php

namespace App\Domains\Currency\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts {"mode":"step","step":"0.05"} or {"mode":"ends_with","value":"0.99"}, and refuses a
 * rule finer than the currency's precision, which rounding would silently undo.
 */
class RoundingRule implements ValidationRule
{
    public function __construct(private int $exponent) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail("The {$attribute} field must be a rounding rule object.");

            return;
        }

        $amount = match ($value['mode'] ?? null) {
            'step' => $value['step'] ?? null,
            'ends_with' => $value['value'] ?? null,
            default => null,
        };

        if (! is_string($amount) || preg_match('/^\d{1,8}(\.(\d{1,6}))?$/', $amount, $parts) !== 1) {
            $fail("The {$attribute} field needs a mode of step or ends_with and a decimal string.");

            return;
        }

        if (strlen(rtrim($parts[2] ?? '', '0')) > $this->exponent) {
            $fail("The {$attribute} field is finer than this currency's {$this->exponent} decimal places.");

            return;
        }

        if ($value['mode'] === 'step' && bccomp($amount, '0', 6) !== 1) {
            $fail('The rounding step must be greater than zero.');
        }

        if ($value['mode'] === 'ends_with' && bccomp($amount, '1', 6) !== -1) {
            $fail('The rounding ending must be less than one.');
        }
    }
}
