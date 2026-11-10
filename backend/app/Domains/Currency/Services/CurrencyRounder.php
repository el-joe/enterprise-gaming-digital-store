<?php

namespace App\Domains\Currency\Services;

use App\Domains\Shared\ValueObjects\Money;

/**
 * Applies a currency's rounding rule and precision to a customer-facing amount.
 */
class CurrencyRounder
{
    private const int WORKING_SCALE = 12;

    /**
     * @param  array{mode?: string, step?: string, value?: string}|null  $rule
     */
    public function round(Money $money, int $exponent, ?array $rule): Money
    {
        $amount = match ($rule['mode'] ?? null) {
            'step' => $this->nearestStep($money->amount, (string) $rule['step']),
            'ends_with' => $this->endingWith($money->amount, (string) $rule['value']),
            default => $money->amount,
        };

        return Money::of($amount, $money->currency)->roundToCurrency($exponent);
    }

    private function nearestStep(string $amount, string $step): string
    {
        $steps = bcadd(bcdiv($amount, $step, self::WORKING_SCALE), '0.5', self::WORKING_SCALE);

        return bcmul(bcdiv($steps, '1', 0), $step, self::WORKING_SCALE);
    }

    /**
     * The smallest amount at or above the input whose fractional part is the given ending.
     */
    private function endingWith(string $amount, string $ending): string
    {
        $candidate = bcadd(bcdiv($amount, '1', 0), $ending, self::WORKING_SCALE);

        return bccomp($candidate, $amount, self::WORKING_SCALE) === -1
            ? bcadd($candidate, '1', self::WORKING_SCALE)
            : $candidate;
    }
}
