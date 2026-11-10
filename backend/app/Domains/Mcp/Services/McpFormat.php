<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Shared\ValueObjects\Money;
use DateTimeInterface;

/**
 * Formatting shared by the MCP resources: money as a decimal string with its currency, never a
 * JSON number (API_DOCUMENTATION.md section 1), and percentages as decimal strings.
 */
final class McpFormat
{
    /**
     * @return array{amount: string, currency: string}|null
     */
    public static function money(mixed $amount, ?string $currency, int $exponent = 2): ?array
    {
        if ($amount === null || $currency === null || $currency === '') {
            return null;
        }

        $money = Money::of((string) $amount, $currency)->roundToCurrency($exponent);

        return ['amount' => bcadd($money->amount, '0', $exponent), 'currency' => $money->currency];
    }

    /**
     * `part / whole * 100` with two decimals, or null when the whole is zero.
     */
    public static function percent(mixed $part, mixed $whole): ?string
    {
        $whole = (string) ($whole ?? '0');

        if (bccomp($whole, '0', 6) === 0) {
            return null;
        }

        $value = bcdiv(bcmul((string) ($part ?? '0'), '100', 10), $whole, 10);

        return bcadd($value, str_starts_with($value, '-') ? '-0.005' : '0.005', 2);
    }

    public static function iso(mixed $date): ?string
    {
        return $date instanceof DateTimeInterface ? $date->format(DATE_ATOM) : null;
    }
}
