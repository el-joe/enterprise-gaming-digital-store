<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Repositories\WalletRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Reading a wallet, and staff adjustments (`customers.wallet_adjust`). An adjustment is a
 * credit or a debit with a reason and a client idempotency key, posted through the ledger and
 * audited. There is no customer top-up (BD3).
 */
class WalletService
{
    public function __construct(
        private WalletRepository $wallets,
        private WalletLedger $ledger,
        private ExchangeRateRegistry $rates,
        private AuditLogger $audit,
    ) {}

    /**
     * The customer's wallet; an empty one in the base currency when none exists yet.
     */
    public function summary(User $user): CustomerWallet
    {
        return $this->wallets->forUser($user->id) ?? (new CustomerWallet)->forceFill([
            'user_id' => $user->id,
            'currency' => $this->rates->baseCurrency(),
            'balance' => '0',
            'status' => CustomerWalletStatus::Active,
        ]);
    }

    public function statement(User $user, int $page, int $perPage): LengthAwarePaginator
    {
        $wallet = $this->wallets->forUser($user->id);

        return $wallet === null
            ? new Paginator([], 0, $perPage, $page)
            : $this->wallets->statement($wallet->id, $page, $perPage);
    }

    public function adjust(User $customer, User $staff, bool $credit, Money $amount, string $reason, string $idempotencyKey): WalletTransaction
    {
        if ($customer->type !== UserType::Customer) {
            throw new ApplicationException('Only customer wallets can be adjusted.', 'conflict', 409);
        }

        return DB::transaction(function () use ($customer, $staff, $credit, $amount, $reason, $idempotencyKey): WalletTransaction {
            $wallet = $this->ledger->lockOrCreate($customer->id);
            $key = 'admin:'.$idempotencyKey;
            $replay = $this->wallets->findByKey($wallet->id, $key) !== null;

            $transaction = $this->ledger->postLocked($wallet, new WalletEntry(
                userId: $customer->id,
                type: $credit ? WalletTransactionType::CreditAdmin : WalletTransactionType::DebitAdmin,
                amount: $amount,
                idempotencyKey: $key,
                referenceType: 'user',
                referenceId: $staff->id,
                note: $reason,
                createdBy: $staff->id,
            ));

            if (! $replay) {
                $this->audit->record(new AuditEntry(
                    action: 'wallet.adjusted',
                    subject: $wallet,
                    after: ['type' => $transaction->type->value, 'amount' => $transaction->amount, 'balance_after' => $transaction->balance_after],
                    reason: $reason,
                ));
            }

            return $transaction;
        });
    }

    public function setStatus(User $customer, CustomerWalletStatus $status, string $reason): CustomerWallet
    {
        return DB::transaction(function () use ($customer, $status, $reason): CustomerWallet {
            $wallet = $this->ledger->lockOrCreate($customer->id);
            $before = $wallet->status;
            $wallet->forceFill(['status' => $status])->save();
            $this->audit->record(new AuditEntry(action: 'wallet.status_changed', subject: $wallet, before: ['status' => $before->value], after: ['status' => $status->value], reason: $reason));

            return $wallet;
        });
    }
}
