<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\DTOs\PaymentSession;
use App\Domains\Payments\Exceptions\GatewayCapabilityNotSupported;
use App\Domains\Payments\Exceptions\PaymentAttemptRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Log;

/**
 * The customer-facing payment steps after placement. None of them can mark an order paid by
 * themselves: `verify` and `capture` ask the provider, server side, and apply what it says.
 */
class CheckoutPaymentService
{
    public function __construct(
        private PaymentAttemptService $attempts,
        private PaymentVerifier $verifier,
        private PaymentGatewayRegistry $registry,
    ) {}

    /**
     * The first attempt, right after the order commits. A failure does not undo the order:
     * the customer can retry payment.
     *
     * @return array{session: ?PaymentSession, error: ?string}
     */
    public function startAfterPlacement(string $orderPublicId, ?string $methodCode): array
    {
        try {
            return ['session' => $this->attempts->start($orderPublicId, null, $methodCode), 'error' => null];
        } catch (ApplicationException $exception) {
            Log::warning('payments.first_attempt_not_started', ['order_public_id' => $orderPublicId, 'code' => $exception->errorCode]);

            return ['session' => null, 'error' => $exception->errorCode];
        }
    }

    public function retry(Order $order, ?string $gatewayKey, ?string $methodCode): PaymentSession
    {
        return $this->attempts->start($order->public_id, $gatewayKey, $methodCode);
    }

    /**
     * The return page's server-side check. A browser arriving here proves nothing; only the
     * provider's answer is applied.
     */
    public function verify(Order $order): void
    {
        $payment = $this->latestAttempt($order);

        if ($payment !== null) {
            $this->verifier->verify($payment, OrderActor::system());
        }
    }

    /**
     * Server capture of an approved PayPal order (architecture 13 step 6).
     */
    public function capture(Order $order): void
    {
        $payment = $this->latestAttempt($order) ?? throw new PaymentAttemptRefused('This order has no payment to capture.', 'payment_not_found', 404);
        $account = PaymentGateway::query()->findOrFail($payment->payment_gateway_id);

        if (! $this->registry->for($account)->capabilities()->capture) {
            throw new GatewayCapabilityNotSupported($account->key, 'capture');
        }

        $this->verifier->verify($payment, OrderActor::system());
    }

    private function latestAttempt(Order $order): ?Payment
    {
        return Payment::query()->where('order_id', $order->id)->whereNotNull('provider_payment_id')->orderByDesc('attempt_no')->first();
    }
}
