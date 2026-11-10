<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Exceptions\WalletOperationRefused;
use App\Domains\Wallet\Repositories\WalletRepository;

/**
 * Wallet refunds (BD3): a refund of a wallet payment, or a staff refund with destination
 * `wallet`, is a `credit_refund` keyed `refund:{refund public id}`. Posting it again returns the
 * same entry, so the M10 refund executor can re-send safely. The amount is credited in the
 * wallet currency (the base currency): the refund's base amount.
 */
class WalletRefundService
{
    public function __construct(private WalletLedger $ledger, private WalletRepository $wallets) {}

    public function credit(Refund $refund): RefundResult
    {
        $userId = $this->ownerOf($refund);

        if ($userId === null) {
            return new RefundResult(RefundStatus::Failed, failureReason: 'wallet_unavailable');
        }

        try {
            $transaction = $this->ledger->post(new WalletEntry(
                userId: $userId,
                type: WalletTransactionType::CreditRefund,
                amount: $this->creditAmount($refund),
                idempotencyKey: self::idempotencyKey($refund),
                referenceType: 'refund',
                referenceId: $refund->id,
                note: 'Refund '.$refund->public_id,
            ));
        } catch (WalletOperationRefused $exception) {
            return new RefundResult(RefundStatus::Failed, failureReason: $exception->errorCode);
        }

        return new RefundResult(RefundStatus::Succeeded, WalletLedger::reference($transaction), Money::of($refund->amount, $refund->currency));
    }

    /**
     * Re-read: `Requested` when the ledger has no entry for this refund yet.
     */
    public function lookup(Refund $refund): RefundResult
    {
        $userId = $this->ownerOf($refund);
        $transaction = $userId === null ? null : $this->wallets->findByKeyForUser($userId, self::idempotencyKey($refund));

        return $transaction === null
            ? new RefundResult(RefundStatus::Requested)
            : new RefundResult(RefundStatus::Succeeded, WalletLedger::reference($transaction), Money::of($refund->amount, $refund->currency));
    }

    public static function idempotencyKey(Refund $refund): string
    {
        return 'refund:'.$refund->public_id;
    }

    private function creditAmount(Refund $refund): Money
    {
        return Money::of($refund->amount_base, (string) Order::query()->whereKey($refund->order_id)->value('base_currency'));
    }

    private function ownerOf(Refund $refund): ?int
    {
        $userId = Order::query()->whereKey($refund->order_id)->value('user_id');

        return $userId === null ? null : (int) $userId;
    }
}
