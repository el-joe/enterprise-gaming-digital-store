<?php

namespace App\Domains\Payments\Adapters\Paymob;

/**
 * Paymob transaction-callback HMAC (INTEGRATIONS.md 4): the values of exactly these 20 fields,
 * in this order, concatenated, then HMAC-SHA512 with the HMAC secret, compared in lower-case
 * hex. Booleans are the literal strings `true` and `false` (PHP would cast `true` to `1`), the
 * misspelling `error_occured` is Paymob's, and a missing value contributes an empty string
 * (UNVERIFIED: confirm with a sandbox callback that has no card data).
 */
final class PaymobHmac
{
    /**
     * Field paths in the processed (POST) callback's `obj`.
     */
    public const array FIELDS = [
        'amount_cents',
        'created_at',
        'currency',
        'error_occured',
        'has_parent_transaction',
        'id',
        'integration_id',
        'is_3d_secure',
        'is_auth',
        'is_capture',
        'is_refunded',
        'is_standalone_payment',
        'is_voided',
        'order.id',
        'owner',
        'pending',
        'source_data.pan',
        'source_data.sub_type',
        'source_data.type',
        'success',
    ];

    /**
     * @param  array<string, mixed>  $transaction  the callback's `obj`
     */
    public static function concatenate(array $transaction): string
    {
        $joined = '';

        foreach (self::FIELDS as $path) {
            $joined .= self::stringify(data_get($transaction, $path));
        }

        return $joined;
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function sign(array $transaction, string $secret): string
    {
        return hash_hmac('sha512', self::concatenate($transaction), $secret);
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function verify(array $transaction, string $provided, string $secret): bool
    {
        return hash_equals(self::sign($transaction, $secret), strtolower(trim($provided)));
    }

    private static function stringify(mixed $value): string
    {
        return match (true) {
            $value === true => 'true',
            $value === false => 'false',
            $value === null => '',
            is_array($value) => '',
            default => (string) $value,
        };
    }
}
