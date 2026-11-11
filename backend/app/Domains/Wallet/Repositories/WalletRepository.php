<?php

namespace App\Domains\Wallet\Repositories;

use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\UniqueConstraintViolationException;

class WalletRepository
{
    public function forUser(int $userId): ?CustomerWallet
    {
        return CustomerWallet::query()->where('user_id', $userId)->first();
    }

    /**
     * Lock the user's wallet row. Callers are inside a transaction and already hold any order
     * and payment locks (lock order in architecture 9.4).
     */
    public function lockForUser(int $userId): ?CustomerWallet
    {
        return CustomerWallet::query()->where('user_id', $userId)->lockForUpdate()->first();
    }

    /**
     * Create an empty wallet unless one exists; a concurrent creator is absorbed by the unique
     * user index.
     */
    public function createIfMissing(int $userId, string $currency): void
    {
        try {
            CustomerWallet::query()->insert([
                'user_id' => $userId,
                'currency' => $currency,
                'balance' => '0',
                'status' => CustomerWalletStatus::Active->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return;
        }
    }

    public function findByKey(int $walletId, string $idempotencyKey): ?WalletTransaction
    {
        return WalletTransaction::query()->where('customer_wallet_id', $walletId)->where('idempotency_key', $idempotencyKey)->first();
    }

    public function findByKeyForUser(int $userId, string $idempotencyKey): ?WalletTransaction
    {
        return WalletTransaction::query()
            ->whereIn('customer_wallet_id', CustomerWallet::query()->select('id')->where('user_id', $userId))
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * Statement, newest first (idx_wt_wallet), with the order behind each purchase debit or
     * refund credit loaded per page (M-A15, `WalletTransactionResource::order`).
     */
    public function statement(int $walletId, int $page, int $perPage): LengthAwarePaginator
    {
        return WalletTransaction::query()
            ->where('customer_wallet_id', $walletId)
            ->with(['reference' => static fn (MorphTo $reference) => $reference->morphWith([
                Payment::class => ['order:id,public_id,order_number,user_id'],
                Refund::class => ['order:id,public_id,order_number,user_id'],
            ])])
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return array{sum: string, entries: int, last_balance_after: ?string}
     */
    public function ledgerTotals(int $walletId): array
    {
        $totals = WalletTransaction::query()->where('customer_wallet_id', $walletId)
            ->selectRaw('COALESCE(SUM(amount), 0) AS total, COUNT(*) AS entries')
            ->first();

        $last = WalletTransaction::query()->where('customer_wallet_id', $walletId)->orderByDesc('id')->value('balance_after');

        return [
            'sum' => (string) $totals?->getAttribute('total'),
            'entries' => (int) $totals?->getAttribute('entries'),
            'last_balance_after' => $last === null ? null : (string) $last,
        ];
    }

    /**
     * @return list<int>
     */
    public function walletIds(int $afterId, int $limit): array
    {
        return CustomerWallet::query()->where('id', '>', $afterId)->orderBy('id')->limit($limit)->pluck('id')->map(static fn ($id): int => (int) $id)->all();
    }
}
