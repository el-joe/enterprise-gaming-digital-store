<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Contracts\InternalLedgerGateway;
use App\Domains\Payments\DTOs\CreatePaymentRequest;
use App\Domains\Payments\DTOs\PaymentSession;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Exceptions\PaymentAttemptRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use App\Domains\Payments\Models\PaymentMethod;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Checkout step 4 (architecture 13): open a payment attempt for a `pending_payment` order and
 * create it at the provider, outside any transaction.
 *
 * The attempt row is committed first with the order's frozen snapshot (base amount, rate,
 * charged amount and currency), so nothing is recomputed. The provider is never asked to charge
 * a currency that the adapter and the merchant account do not both support. A create that
 * fails before sending, or is refused, marks the attempt failed; one whose outcome is unknown
 * marks it expired: no customer was given its client secret, so it cannot be paid, and a new
 * attempt is needed (for Paymob, which has no idempotency, this is the documented recovery).
 *
 * An internal-ledger gateway (the wallet, M11) has no provider: the committed attempt is settled
 * by the gateway itself, which debits the ledger and applies the result in one transaction.
 */
class PaymentAttemptService
{
    public function __construct(
        private PaymentGatewayRegistry $registry,
        private SettingsService $settings,
    ) {}

    /**
     * @throws PaymentAttemptRefused
     * @throws GatewayNotConfigured
     */
    public function start(string $orderPublicId, ?string $gatewayKey = null, ?string $methodCode = null): PaymentSession
    {
        $payment = $this->openAttempt($orderPublicId, $gatewayKey, $methodCode);
        $order = Order::query()->findOrFail($payment->order_id);
        $account = PaymentGateway::query()->findOrFail($payment->payment_gateway_id);
        $adapter = $this->registry->for($account);

        if ($adapter instanceof InternalLedgerGateway) {
            return $adapter->settle($payment);
        }

        try {
            $gatewayAmount = $adapter->toGatewayAmount(Money::of($payment->amount, $payment->currency));
        } catch (InvalidArgumentException) {
            $this->close($payment, PaymentStatus::Failed, 'amount_not_representable');

            throw new GatewayNotConfigured('The amount cannot be charged with this payment method.');
        }

        try {
            $result = $adapter->createPayment(new CreatePaymentRequest(
                payment: $payment,
                order: $order,
                gatewayAmount: $gatewayAmount,
                currency: $payment->currency,
                idempotencyKey: 'pay_create:'.$payment->public_id,
                returnUrl: $this->returnUrl($order),
                notificationUrl: route('webhooks.payments', ['gateway' => $account->key]),
                description: 'Order '.$order->order_number,
                customerEmail: $order->contact_email,
                customerName: $order->customer_name,
                customerPhone: $order->contact_phone,
                methodCode: $payment->method_code,
                integrationRefs: $this->integrationRefs($account, $payment),
            ));
        } catch (GatewayRequestFailed $exception) {
            $this->close($payment, $exception->ambiguous ? PaymentStatus::Expired : PaymentStatus::Failed, $exception->ambiguous ? 'create_unknown' : 'create_refused');
            Log::warning('payments.create_failed', ['payment_id' => $payment->id, 'ambiguous' => $exception->ambiguous, 'error' => $exception->getMessage()]);

            throw new GatewayNotConfigured('The payment could not be started. Please try again.');
        } catch (GatewayNotConfigured $exception) {
            $this->close($payment, PaymentStatus::Failed, 'gateway_unavailable');

            throw $exception;
        }

        $payment->forceFill([
            'provider_payment_id' => $result->providerPaymentId,
            'provider_order_id' => $result->providerOrderId,
            'provider_reference' => $result->providerReference,
            'status' => PaymentStatus::RequiresAction,
            'next_check_at' => now()->addMinutes((int) config('payments.reconcile_open_after_minutes')),
        ])->save();

        return new PaymentSession($payment->public_id, $account->key, $result->next);
    }

    private function openAttempt(string $orderPublicId, ?string $gatewayKey, ?string $methodCode): Payment
    {
        return DB::transaction(function () use ($orderPublicId, $gatewayKey, $methodCode): Payment {
            $order = Order::query()->where('public_id', $orderPublicId)->lockForUpdate()->firstOrFail();

            if ($order->status !== OrderStatus::PendingPayment || $order->primary_payment_id !== null) {
                throw new PaymentAttemptRefused('This order is not awaiting payment.', 'order_not_payable');
            }

            if ($order->expires_at !== null && $order->expires_at->isPast()) {
                throw new PaymentAttemptRefused('This order has expired. Please place it again.', 'order_expired');
            }

            $account = PaymentGateway::query()->findOrFail($order->payment_gateway_id);

            if ($gatewayKey !== null && $gatewayKey !== $account->key) {
                throw new PaymentAttemptRefused('Changing the payment method of a placed order is not supported; place the order again.', 'gateway_change_unsupported', 422);
            }

            $this->assertChargeable($account, (string) $order->payment_currency);

            $attempts = Payment::query()->where('order_id', $order->id)->count();

            if ($attempts >= (int) $this->settings->get('orders', 'max_payment_attempts', 5)) {
                throw new PaymentAttemptRefused('No payment attempts remain for this order.', 'payment_attempts_exhausted');
            }

            $payment = (new Payment)->forceFill([
                'order_id' => $order->id,
                'payment_gateway_id' => $account->id,
                'attempt_no' => (int) Payment::query()->where('order_id', $order->id)->max('attempt_no') + 1,
                'status' => PaymentStatus::Created,
                'method_code' => $methodCode,
                'base_amount' => $order->total_base,
                'base_currency' => $order->base_currency,
                'rate' => $order->payment_rate,
                'amount' => $order->payment_total,
                'currency' => $order->payment_currency,
                'fee_is_estimate' => true,
                'expires_at' => $order->expires_at,
            ]);
            $payment->save();

            return $payment;
        }, 3);
    }

    /**
     * Never charge a gateway in a currency it does not support (spec Phase 7): both the adapter
     * and the merchant account must allow it, checked again at the moment of charging.
     */
    private function assertChargeable(PaymentGateway $account, string $currency): void
    {
        $enabled = PaymentGatewayCurrency::query()->where('payment_gateway_id', $account->id)->where('currency', $currency)->where('is_enabled', true)->exists();

        if (! $account->is_active || ! $enabled || ! $this->registry->has($account->adapter_key) || ! $this->registry->for($account)->supportsCurrency($currency)) {
            throw new PaymentAttemptRefused('This payment method cannot charge this order.', 'gateway_not_eligible', 422);
        }
    }

    /**
     * @return list<string>
     */
    private function integrationRefs(PaymentGateway $account, Payment $payment): array
    {
        $methods = PaymentMethod::query()->where('payment_gateway_id', $account->id)->where('is_active', true)
            ->when($payment->method_code !== null, static fn ($query) => $query->where('code', $payment->method_code))
            ->whereNotNull('integration_ref')->pluck('integration_ref')->all();

        if ($methods !== []) {
            return array_values(array_map('strval', $methods));
        }

        $currencyRef = PaymentGatewayCurrency::query()->where('payment_gateway_id', $account->id)->where('currency', $payment->currency)->value('integration_ref');

        return $currencyRef === null ? [] : [(string) $currencyRef];
    }

    private function close(Payment $payment, PaymentStatus $status, string $code): void
    {
        $payment->forceFill(['status' => $status, 'failure_code' => $code, 'next_check_at' => null])->save();
    }

    private function returnUrl(Order $order): string
    {
        return url(str_replace('{order}', $order->public_id, (string) config('payments.return_url')));
    }
}
