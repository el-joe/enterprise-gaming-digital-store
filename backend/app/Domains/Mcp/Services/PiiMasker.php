<?php

namespace App\Domains\Mcp\Services;

use Illuminate\Support\Str;

/**
 * Masks customer identifiers in MCP responses (MCP_SERVER.md section 3): `j***@example.com`,
 * `***67` and `J***`. Values are never returned unmasked through MCP.
 */
class PiiMasker
{
    public function email(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        $at = strrpos($email, '@');

        if ($at === false || $at === 0) {
            return '***';
        }

        return mb_substr($email, 0, 1).'***'.substr($email, $at);
    }

    public function phone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return '***'.substr($digits, -2);
    }

    public function name(?string $name): ?string
    {
        $name = trim((string) $name);

        return $name === '' ? null : Str::upper(mb_substr($name, 0, 1)).'***';
    }

    /**
     * Mask every `email`, `phone` or `name` key of a flat argument list before it is audited.
     *
     * @param  array<array-key, mixed>  $arguments
     * @return array<array-key, mixed>
     */
    public function arguments(array $arguments): array
    {
        foreach ($arguments as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $arguments[$key] = match (true) {
                str_contains((string) $key, 'email') => $this->email($value),
                str_contains((string) $key, 'phone') => $this->phone($value),
                default => $value,
            };
        }

        return $arguments;
    }
}
