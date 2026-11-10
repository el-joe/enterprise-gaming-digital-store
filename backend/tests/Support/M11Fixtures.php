<?php

/**
 * Fixtures for M11: wallet, coupons, affiliates, reviews and support.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Services\WalletLedger;
use Database\Factories\Currency\CurrencyFactory;

/**
 * Credit a customer's wallet through the ledger, as a refund would.
 */
function fundWallet(User $user, string $amount, ?string $key = null): WalletTransaction
{
    CurrencyFactory::ensure('USD');

    return app(WalletLedger::class)->post(new WalletEntry(
        userId: $user->id,
        type: WalletTransactionType::CreditRefund,
        amount: Money::of($amount, 'USD'),
        idempotencyKey: $key ?? 'fixture:'.bin2hex(random_bytes(6)),
    ));
}

function walletOf(User $user): ?CustomerWallet
{
    return CustomerWallet::query()->where('user_id', $user->id)->first();
}

/**
 * The balance column and the ledger sum, for "balance equals the sum of entries" assertions.
 *
 * @return array{balance: string, sum: string}
 */
function walletTotals(User $user): array
{
    $wallet = walletOf($user);

    return [
        'balance' => $wallet === null ? '0.000000' : (string) $wallet->fresh()->balance,
        'sum' => $wallet === null ? '0.000000' : bcadd((string) WalletTransaction::query()->where('customer_wallet_id', $wallet->id)->sum('amount'), '0', 6),
    ];
}

/**
 * The wallet gateway, active, charging USD, allowed for signed-in customers only (as seeded).
 */
function walletGateway(): PaymentGateway
{
    return gatewayFor('wallet', ['USD'], rules: [['priority' => 100, 'effect' => 'allow', 'allow_guest' => false]]);
}
