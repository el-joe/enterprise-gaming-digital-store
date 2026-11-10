<?php

namespace App\Domains\Shared\Services;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * Replaces the values of sensitive keys (SECURITY.md section 9) anywhere in a nested structure.
 */
class SensitiveDataRedactor
{
    public const string REPLACEMENT = '[REDACTED]';

    private const string PATTERN = '/(password|passwd|token|secret|api_?key|authorization|cookie|ciphertext)'
        .'|^card|^pin(_|$)|^redemption|^code$|^cvv|^otp'
        .'|(^|_)(digital|gift|recovery|voucher|activation)_codes?$/';

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && $this->isSensitiveKey($key)) {
                $data[$key] = self::REPLACEMENT;

                continue;
            }

            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if (is_array($value)) {
                $data[$key] = $this->redact($value);
            }
        }

        return $data;
    }

    public function isSensitiveKey(string $key): bool
    {
        return preg_match(self::PATTERN, Str::snake(str_replace(['-', ' '], '_', $key))) === 1;
    }
}
