<?php

namespace App\Domains\Wallet\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * A wallet movement that was not posted: insufficient balance, a frozen wallet, a zero or
 * wrong-currency amount, or an idempotency key reused for a different movement.
 */
class WalletOperationRefused extends ApplicationException
{
    public static function insufficientBalance(): self
    {
        return new self('The wallet balance is not enough for this payment.', 'wallet_insufficient_balance', 422);
    }

    public static function frozen(): self
    {
        return new self('This wallet is frozen.', 'wallet_frozen', 409);
    }

    public static function unavailable(string $message = 'The wallet cannot be used for this order.'): self
    {
        return new self($message, 'wallet_unavailable', 422);
    }

    public static function invalidAmount(): self
    {
        return new self('The amount must be positive and in the wallet currency.', 'wallet_amount_invalid', 422);
    }

    public static function keyReused(): self
    {
        return new self('This idempotency key was already used for a different wallet movement.', 'wallet_idempotency_mismatch', 409);
    }
}
