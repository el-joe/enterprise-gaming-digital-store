<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use App\Domains\Wallet\Exceptions\WalletOperationRefused;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;

/**
 * The wallet ledger (architecture 14, BD3: credits and refunds only, no customer top-up).
 *
 * Every movement locks the wallet row, appends one `wallet_transactions` row with its
 * `balance_after`, and writes the new balance, in one transaction; so the balance always equals
 * the sum of the entries, and two debits can never both pass the balance check. The balance may
 * never go below zero (also enforced by `chk_cw_balance_nonneg`). An idempotency key is unique
 * per wallet: a repeated key returns the first entry unchanged, and a key reused for a different
 * movement is refused.
 */
class WalletLedger
{
    public function __construct(private WalletRepository $wallets, private ExchangeRateRegistry $rates) {}

    /**
     * Post a movement in its own transaction. Credits create the wallet on first use.
     */
    public function post(WalletEntry $entry): WalletTransaction
    {
        return DB::transaction(function () use ($entry): WalletTransaction {
            $wallet = $entry->isCredit() ? $this->lockOrCreate($entry->userId) : $this->wallets->lockForUser($entry->userId);

            if ($wallet === null) {
                throw WalletOperationRefused::insufficientBalance();
            }

            return $this->postLocked($wallet, $entry);
        }, 3);
    }

    /**
     * Lock the wallet, creating it in the base currency when missing. Inside a transaction.
     */
    public function lockOrCreate(int $userId): CustomerWallet
    {
        $wallet = $this->wallets->lockForUser($userId);

        if ($wallet !== null) {
            return $wallet;
        }

        $this->wallets->createIfMissing($userId, $this->rates->baseCurrency());

        return $this->wallets->lockForUser($userId) ?? throw WalletOperationRefused::unavailable();
    }

    /**
     * Post against a wallet the caller has locked in its own transaction (wallet payments lock
     * the order and payment first).
     */
    public function postLocked(CustomerWallet $wallet, WalletEntry $entry): WalletTransaction
    {
        $existing = $this->wallets->findByKey($wallet->id, $entry->idempotencyKey);

        if ($existing !== null) {
            if ($existing->type !== $entry->type || bccomp($existing->amount, $entry->signedAmount()->amount, Money::SCALE) !== 0) {
                throw WalletOperationRefused::keyReused();
            }

            return $existing;
        }

        if (! $entry->amount->isPositive() || $entry->amount->currency !== $wallet->currency) {
            throw WalletOperationRefused::invalidAmount();
        }

        if (! $entry->isCredit() && $wallet->status === CustomerWalletStatus::Frozen) {
            throw WalletOperationRefused::frozen();
        }

        $balance = Money::of($wallet->balance, $wallet->currency)->plus($entry->signedAmount());

        if ($balance->isNegative()) {
            throw WalletOperationRefused::insufficientBalance();
        }

        $transaction = (new WalletTransaction)->forceFill([
            'customer_wallet_id' => $wallet->id,
            'type' => $entry->type,
            'amount' => $entry->signedAmount()->amount,
            'balance_after' => $balance->amount,
            'reference_type' => $entry->referenceType,
            'reference_id' => $entry->referenceId,
            'idempotency_key' => $entry->idempotencyKey,
            'note' => $entry->note === null ? null : mb_substr($entry->note, 0, 255),
            'created_by' => $entry->createdBy,
        ]);
        $transaction->save();

        CustomerWallet::query()->whereKey($wallet->id)->update(['balance' => $balance->amount, 'updated_at' => now()]);
        $wallet->setRawAttributes([...$wallet->getAttributes(), 'balance' => $balance->amount], sync: true);

        return $transaction;
    }

    /**
     * The balance a payment could use now, or null when the user has no usable wallet.
     */
    public function spendableBalance(int $userId): ?Money
    {
        $wallet = $this->wallets->forUser($userId);

        if ($wallet === null || $wallet->status !== CustomerWalletStatus::Active) {
            return null;
        }

        return Money::of($wallet->balance, $wallet->currency);
    }

    /**
     * Whether the stored balance equals the sum of the entries and the last `balance_after`.
     */
    public function isConsistent(CustomerWallet $wallet): bool
    {
        $totals = $this->wallets->ledgerTotals($wallet->id);
        $balance = (string) $wallet->balance;

        if (bccomp($totals['sum'], $balance, Money::SCALE) !== 0) {
            return false;
        }

        return $totals['entries'] === 0
            ? bccomp($balance, '0', Money::SCALE) === 0
            : bccomp((string) $totals['last_balance_after'], $balance, Money::SCALE) === 0;
    }

    /**
     * Ledger reference used as the provider id of wallet payments and refunds.
     */
    public static function reference(WalletTransaction $transaction): string
    {
        return 'wallet_txn_'.$transaction->id;
    }
}
