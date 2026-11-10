<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\PaymentTransactionStatus;
use App\Domains\Payments\Enums\PaymentTransactionType;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies a server-side provider read to our payment and order, idempotently, under the lock
 * order `orders`, `payments` (architecture 9.4). This is the only code that marks a payment
 * succeeded or an order paid, and it only ever receives state read by the server from the
 * provider (a re-read, or a server-initiated capture response); never browser input.
 *
 * - Succeeded with amount and currency equal to the attempt's snapshot: the payment takes the
 *   order's unique `primary_payment_id` slot and the order moves to `paid` (fulfilment starts).
 * - Succeeded with a different amount or currency: the payment is recorded, the order goes to
 *   `manual_review`, finance is alerted. Never paid.
 * - Succeeded on a cancelled or failed order (late payment): `manual_review`, never fulfilled.
 * - Succeeded while another payment holds the slot: `duplicate_capture`, a full automatic refund.
 * - A failure after success (out of order) is ignored; a success after a failure is accepted,
 *   since the provider's current state is the truth.
 */
class PaymentStateApplier
{
    private const array SETTLED = [PaymentStatus::Succeeded, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded, PaymentStatus::DuplicateCapture];

    public function __construct(
        private OrderStateMachine $machine,
        private RefundService $refunds,
        private GatewayFeeEstimator $fees,
        private SettingsService $settings,
    ) {}

    public function apply(int $paymentId, VerifiedPaymentState $state, OrderActor $actor): Payment
    {
        $orderId = (int) Payment::query()->whereKey($paymentId)->value('order_id');

        return DB::transaction(function () use ($orderId, $paymentId, $state, $actor): Payment {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            $payment = Payment::query()->lockForUpdate()->findOrFail($paymentId);

            $payment->forceFill(array_filter([
                'verified_at' => now(),
                'next_check_at' => null,
                'provider_capture_id' => $payment->provider_capture_id ?? $state->providerCaptureId,
                'card_country' => $state->cardCountry ?? $payment->card_country,
                'card_fingerprint_hash' => $state->cardFingerprint !== null ? hash_hmac('sha256', $state->cardFingerprint, (string) config('app.key')) : $payment->card_fingerprint_hash,
            ], static fn (mixed $value): bool => $value !== null));

            match ($state->status) {
                ProviderPaymentStatus::Succeeded => $this->succeeded($order, $payment, $state, $actor),
                ProviderPaymentStatus::Processing => $this->processing($order, $payment, $actor),
                ProviderPaymentStatus::RequiresAction, ProviderPaymentStatus::Approved => $this->awaitingCustomer($payment),
                ProviderPaymentStatus::Failed, ProviderPaymentStatus::Cancelled => $this->failed($order, $payment, $state, $actor),
                ProviderPaymentStatus::Pending, ProviderPaymentStatus::NotFound => null,
            };

            $payment->save();

            return $payment;
        }, 3);
    }

    private function succeeded(Order $order, Payment $payment, VerifiedPaymentState $state, OrderActor $actor): void
    {
        if (in_array($payment->status, self::SETTLED, true)) {
            return;
        }

        $expected = Money::of($payment->amount, $payment->currency);
        $matches = $state->amount !== null && $state->amount->equals($expected);

        $this->ledger($payment, PaymentTransactionType::Capture, $state->providerCaptureId ?? $payment->provider_payment_id, $state->amount ?? $expected, $state->raw);
        $this->recordFee($order, $payment, $state);

        if ($order->primary_payment_id !== null && $order->primary_payment_id !== $payment->id) {
            $payment->forceFill(['status' => PaymentStatus::DuplicateCapture])->save();
            $this->note($order, $actor, 'payment.duplicate_capture', 'A second payment succeeded on this order; it is refunded automatically.', ['payment_id' => $payment->id, 'primary_payment_id' => $order->primary_payment_id]);
            Log::alert('payments.duplicate_capture', ['order_id' => $order->id, 'payment_id' => $payment->id]);
            $this->refunds->refundDuplicate($payment);

            return;
        }

        $payment->forceFill([
            'status' => PaymentStatus::Succeeded,
            'failure_code' => $matches ? null : 'amount_mismatch',
            'failure_message' => $matches ? null : 'Provider amount or currency differs from the attempt.',
        ])->save();

        Order::query()->whereKey($order->id)->update(['primary_payment_id' => $payment->id]);
        $order->primary_payment_id = $payment->id;

        $metadata = ['payment_id' => $payment->id, 'gateway_id' => $payment->payment_gateway_id];

        if (! $matches) {
            Log::alert('payments.amount_mismatch', [...$metadata, 'order_id' => $order->id, 'expected' => (string) $expected, 'received' => $state->amount === null ? null : (string) $state->amount]);
            $this->toReview($order, $actor, 'Payment amount or currency does not match the order.', [...$metadata, 'reason' => 'amount_mismatch', 'expected' => (string) $expected, 'received' => $state->amount === null ? null : (string) $state->amount]);

            return;
        }

        if (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Failed], true)) {
            Log::alert('payments.late_payment', [...$metadata, 'order_id' => $order->id, 'order_status' => $order->status->value]);
            $this->toReview($order, $actor, 'Verified payment arrived after the order closed. Refund or approve manually.', [...$metadata, 'reason' => 'late_payment']);

            return;
        }

        if (in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentProcessing], true)) {
            $this->machine->transition($order, OrderStatus::Paid, $actor, 'Payment verified with the provider.', $metadata);

            return;
        }

        Log::alert('payments.success_on_unexpected_status', [...$metadata, 'order_id' => $order->id, 'order_status' => $order->status->value]);
    }

    private function processing(Order $order, Payment $payment, OrderActor $actor): void
    {
        if (! in_array($payment->status, [PaymentStatus::Created, PaymentStatus::RequiresAction], true)) {
            return;
        }

        $payment->forceFill(['status' => PaymentStatus::Processing]);

        if ($order->status === OrderStatus::PendingPayment && $order->primary_payment_id === null) {
            $this->machine->transition($order, OrderStatus::PaymentProcessing, $actor, 'Provider is processing the payment.', ['payment_id' => $payment->id]);
        }
    }

    private function awaitingCustomer(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::Created) {
            $payment->forceFill(['status' => PaymentStatus::RequiresAction]);
        }
    }

    private function failed(Order $order, Payment $payment, VerifiedPaymentState $state, OrderActor $actor): void
    {
        if (in_array($payment->status, self::SETTLED, true)) {
            Log::warning('payments.failure_after_success_ignored', ['payment_id' => $payment->id, 'status' => $payment->status->value]);

            return;
        }

        if (in_array($payment->status, [PaymentStatus::Failed, PaymentStatus::Cancelled, PaymentStatus::Expired], true)) {
            return;
        }

        $payment->forceFill([
            'status' => $state->status === ProviderPaymentStatus::Cancelled ? PaymentStatus::Cancelled : PaymentStatus::Failed,
            'failure_code' => mb_substr((string) ($state->failureCode ?? 'declined'), 0, 64),
            'failure_message' => $state->failureMessage === null ? null : mb_substr($state->failureMessage, 0, 255),
        ]);

        if ($order->primary_payment_id !== null || ! in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentProcessing], true)) {
            return;
        }

        $openAttempts = Payment::query()->where('order_id', $order->id)->whereKeyNot($payment->id)
            ->whereIn('status', [PaymentStatus::Created, PaymentStatus::RequiresAction, PaymentStatus::Processing])->exists();

        if ($openAttempts) {
            return;
        }

        $exhausted = Payment::query()->where('order_id', $order->id)->count() >= (int) $this->settings->get('orders', 'max_payment_attempts', 5);

        if ($exhausted) {
            $this->machine->transition($order, OrderStatus::Failed, OrderActor::system(), 'Payment attempts exhausted.', ['payment_id' => $payment->id]);
        } elseif ($order->status === OrderStatus::PaymentProcessing) {
            $this->machine->transition($order, OrderStatus::PendingPayment, $actor, 'Payment attempt failed; the customer may retry.', ['payment_id' => $payment->id]);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function toReview(Order $order, OrderActor $actor, string $message, array $metadata): void
    {
        if (in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentProcessing, OrderStatus::Cancelled, OrderStatus::Failed], true)) {
            $this->machine->transition($order, OrderStatus::ManualReview, $actor, $message, $metadata);

            return;
        }

        $this->note($order, $actor, 'payment.review_required', $message, $metadata);
    }

    private function recordFee(Order $order, Payment $payment, VerifiedPaymentState $state): void
    {
        if ($state->fee !== null) {
            $payment->forceFill(['fee_amount' => $state->fee->amount, 'fee_currency' => $state->fee->currency, 'fee_is_estimate' => false]);

            return;
        }

        $estimate = $this->fees->estimate($payment, $order->country_code);
        $payment->forceFill(['fee_amount' => $estimate->amount, 'fee_currency' => $estimate->currency, 'fee_is_estimate' => true]);
    }

    /**
     * One ledger row per provider movement; the unique key absorbs repeats.
     *
     * @param  array<string, mixed>  $raw
     */
    public function ledger(Payment $payment, PaymentTransactionType $type, ?string $providerTxnId, Money $amount, array $raw = [], PaymentTransactionStatus $status = PaymentTransactionStatus::Succeeded): void
    {
        PaymentTransaction::query()->insertOrIgnore([
            'payment_id' => $payment->id,
            'type' => $type->value,
            'status' => $status->value,
            'provider_txn_id' => $providerTxnId,
            'amount' => $amount->amount,
            'currency' => $amount->currency,
            'raw_redacted' => $raw === [] ? null : json_encode($raw, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function note(Order $order, OrderActor $actor, string $type, string $message, array $metadata): void
    {
        (new OrderEvent)->forceFill([
            'order_id' => $order->id,
            'type' => $type,
            'actor_type' => $actor->type,
            'actor_id' => $actor->id(),
            'message' => $message,
            'metadata' => $metadata,
        ])->save();
    }
}
