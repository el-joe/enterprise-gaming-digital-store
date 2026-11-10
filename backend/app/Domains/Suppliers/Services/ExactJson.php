<?php

namespace App\Domains\Suppliers\Services;

use JsonException;

/**
 * Decodes supplier JSON with every number kept as its literal text. Supplier amounts carry five
 * decimal places and card numbers are typed as integers (INTEGRATIONS.md 1.4), so a float or an
 * integer cast could change them; strings cannot.
 */
final class ExactJson
{
    private const string TOKENS = '/"(?:[^"\\\\]|\\\\.)*"|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?/';

    /**
     * @return array<array-key, mixed>|null null when the body is not a JSON object or array
     */
    public static function decode(string $body): ?array
    {
        $quoted = preg_replace_callback(self::TOKENS, static fn (array $match): string => $match[0][0] === '"' ? $match[0] : '"'.$match[0].'"', $body);

        if ($quoted === null) {
            return null;
        }

        try {
            $decoded = json_decode($quoted, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * A decimal string from a literal, with exponent notation expanded, or null when the value is
     * not a number.
     */
    public static function decimal(mixed $value): ?string
    {
        if (! is_string($value) || preg_match('/^(-?)(\d+(?:\.\d+)?)(?:[eE]([+-]?\d{1,2}))?$/', $value, $parts) !== 1) {
            return null;
        }

        $number = $parts[2];

        if (($parts[3] ?? '') !== '') {
            $number = bcmul($number, bcpow('10', $parts[3], 30), 30);
        }

        $number = str_contains($number, '.') ? rtrim(rtrim($number, '0'), '.') : ltrim($number, '0');
        $number = $number === '' || str_starts_with($number, '.') ? '0'.$number : $number;

        return $parts[1] === '-' && $number !== '0' ? '-'.$number : $number;
    }

    /**
     * Digits of an identifier exactly as sent.
     */
    public static function identifier(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9._:-]{1,128}$/', $value) === 1 ? $value : null;
    }
}
