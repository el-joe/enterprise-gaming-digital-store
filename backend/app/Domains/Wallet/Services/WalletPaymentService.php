<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\DTOs\PaymentSession;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Exceptions\PaymentAttemptRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Exceptions\WalletOperationRefused;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pays an order from the customer's wallet. The debit is the server-side verification: under the
 * lock order `orders`, `payments`, `customer_wallets`, the ledger is debited and the result of
 * that debit is applied through PaymentStateApplier (the only path to `paid`) in the same
 * transaction. Nothing from the browser is trusted, and there is no state in which the wallet was
 * debited but the order not paid, or the reverse.
 */
class WalletPaymentService
{
    public function __construct(
        private WalletLedger $ledger,
        private WalletRepository $wallets,
        private PaymentStateApplier $applier,
    ) {}

    public function pay(Payment $payment, string $gatewayKey): PaymentSession
    {
        try {
            return DB::transaction(fn (): PaymentSession => $this->payLocked($payment->id, $gatewayKey), 3);
        } catch (ApplicationException $exception) {
            Payment::query()->whereKey($payment->id)->where('status', PaymentStatus::Created->value)->update([
                'status' => PaymentStatus::Failed->value,
                'failure_code' => mb_substr($exception->errorCode, 0, 64),
                'next_check_at' => null,
                'updated_at' => now(),
            ]);
            Log::info('wallet.payment_refused', ['payment_id' => $payment->id, 'code' => $exception->errorCode]);

            throw $exception;
        }
    }

    /**
     * The ledger debit made for this attempt, if any: the wallet's source of truth for a re-read.
     */
    public function recordedDebit(Payment $payment): ?WalletTransaction
    {
        $userId = Order::query()->whereKey($payment->order_id)->value('user_id');

        return $userId === null ? null : $this->wallets->findByKeyForUser((int) $userId, self::idempotencyKey($payment));
    }

    public static function idempotencyKey(Payment $payment): string
    {
        return 'pay:'.$payment->public_id;
    }

    private function payLocked(int $paymentId, string $gatewayKey): PaymentSession
    {
        $orderId = (int) Payment::query()->whereKey($paymentId)->value('order_id');
        $order = Order::query()->lockForUpdate()->findOrFail($orderId);
        $payment = Payment::query()->lockForUpdate()->findOrFail($paymentId);

        if ($payment->status !== PaymentStatus::Created || $order->status !== OrderStatus::PendingPayment || $order->primary_payment_id !== null) {
            throw new PaymentAttemptRefused('This order is not awaiting payment.', 'order_not_payable');
        }

        $user = $order->user_id === null ? null : User::query()->find($order->user_id);

        if ($user === null || $user->email_verified_at === null || $user->status !== UserStatus::Active) {
            throw WalletOperationRefused::unavailable('Sign in with a verified account to pay from your wallet.');
        }

        $wallet = $this->wallets->lockForUser($user->id) ?? throw WalletOperationRefused::insufficientBalance();
        $amount = Money::of($payment->amount, $payment->currency);

        $transaction = $this->ledger->postLocked($wallet, new WalletEntry(
            userId: $user->id,
            type: WalletTransactionType::DebitPurchase,
            amount: $amount,
            idempotencyKey: self::idempotencyKey($payment),
            referenceType: 'payment',
            referenceId: $payment->id,
            note: 'Order '.$order->order_number,
        ));

        $reference = WalletLedger::reference($transaction);
        $payment->forceFill(['provider_payment_id' => $reference])->save();

        $this->applier->apply($payment->id, new VerifiedPaymentState(
            status: ProviderPaymentStatus::Succeeded,
            amount: Money::of(bcmul($transaction->amount, '-1', Money::SCALE), $wallet->currency),
            providerCaptureId: $reference,
            fee: Money::zero($wallet->currency),
            raw: ['wallet_transaction_id' => $transaction->id],
        ), OrderActor::system());

        return new PaymentSession($payment->public_id, $gatewayKey, ['type' => 'wallet']);
    }
}
