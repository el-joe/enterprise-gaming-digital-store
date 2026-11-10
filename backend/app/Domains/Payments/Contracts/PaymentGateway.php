<?php

namespace App\Domains\Payments\Contracts;

use App\Domains\Payments\DTOs\CreatePaymentRequest;
use App\Domains\Payments\DTOs\CreatePaymentResult;
use App\Domains\Payments\DTOs\GatewayCapabilities;
use App\Domains\Payments\DTOs\GatewayEvent;
use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\DTOs\VerifiedWebhook;
use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Exceptions\GatewayCapabilityNotSupported;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Exceptions\InvalidWebhookSignature;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;

/**
 * One payment provider account (PROJECT_ARCHITECTURE.md 9.2). Implementations make provider
 * calls only; they never write order or payment state. Every create, capture and refund carries
 * a provider idempotency key where the provider documents one (architecture 9.3).
 */
interface PaymentGateway
{
    public function key(): string;

    public function capabilities(): GatewayCapabilities;

    /**
     * Provider-level currency support, independent of the merchant account. The merchant's own
     * list (`payment_gateway_currencies`) is checked as well; both must allow a currency.
     */
    public function supportsCurrency(string $currency): bool;

    /**
     * The amount in the provider's own unit: integer minor units, or a decimal string (PayPal).
     * Refuses an amount with more decimals than the provider accepts instead of rounding it.
     */
    public function toGatewayAmount(Money $money): int|string;

    /**
     * @throws GatewayRequestFailed
     * @throws GatewayNotConfigured
     */
    public function createPayment(CreatePaymentRequest $request): CreatePaymentResult;

    /**
     * Server-initiated capture of an approved payment (PayPal only).
     *
     * @throws GatewayCapabilityNotSupported
     * @throws GatewayRequestFailed
     */
    public function capture(Payment $payment): VerifiedPaymentState;

    /**
     * Re-read the payment from the provider. The only source of truth for marking paid.
     *
     * @throws GatewayRequestFailed
     */
    public function fetchState(Payment $payment): VerifiedPaymentState;

    /**
     * Verify the signature over the raw body before anything else is trusted.
     *
     * @throws InvalidWebhookSignature
     * @throws GatewayRequestFailed when verification itself could not be completed
     */
    public function verifyWebhook(WebhookRequestData $request): VerifiedWebhook;

    public function parseWebhook(VerifiedWebhook $webhook): GatewayEvent;

    /**
     * @param  int|string  $gatewayAmount  amount in the provider's own unit (minor units or decimal string)
     *
     * @throws GatewayCapabilityNotSupported
     * @throws GatewayRequestFailed
     */
    public function refund(Refund $refund, Payment $payment, int|string $gatewayAmount): RefundResult;

    /**
     * Re-read a refund's state, including one whose create response was lost.
     *
     * @throws GatewayRequestFailed
     */
    public function fetchRefund(Refund $refund, Payment $payment): RefundResult;
}
