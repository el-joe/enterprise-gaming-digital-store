<?php

namespace App\Domains\Payments\Adapters\PayPal;

use App\Domains\Payments\Adapters\AbstractPaymentGateway;
use App\Domains\Payments\DTOs\CreatePaymentRequest;
use App\Domains\Payments\DTOs\CreatePaymentResult;
use App\Domains\Payments\DTOs\DisputeData;
use App\Domains\Payments\DTOs\GatewayCapabilities;
use App\Domains\Payments\DTOs\GatewayEvent;
use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\DTOs\VerifiedWebhook;
use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Enums\GatewayEventKind;
use App\Domains\Payments\Enums\PaymentDisputeStatus;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Exceptions\InvalidWebhookSignature;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * PayPal Orders v2 (INTEGRATIONS.md 3). Status: mocked tests only, awaiting sandbox verification.
 *
 * - Create `POST /v2/checkout/orders` (intent CAPTURE, one purchase unit, `custom_id` = payment
 *   public id, `invoice_id` = `{order_number}-{attempt}`), `PayPal-Request-Id: pay_create:{id}`.
 * - Capture is server-initiated: `POST /v2/checkout/orders/{id}/capture` with
 *   `PayPal-Request-Id: pay_capture:{id}`. Paid only on capture `COMPLETED`; an order
 *   `COMPLETED` with a `PENDING` capture is processing, never paid.
 * - Webhooks verified with `POST /v1/notifications/verify-webhook-signature`, passing the raw
 *   event body unmodified. (INTEGRATIONS.md preferred offline verification; the postback API
 *   was chosen for M10 because it needs no certificate handling. It does not work for the
 *   dashboard's mock events, which therefore cannot be used to test.)
 * - Refunds `POST /v2/payments/captures/{id}/refund` with `PayPal-Request-Id: refund:{id}`.
 * - Never charges EGP, SAR or AED: they are not PayPal currencies.
 */
class PayPalPaymentGateway extends AbstractPaymentGateway
{
    /**
     * PayPal's supported currency list (INTEGRATIONS.md 3). BRL, CNY and MYR are in-country
     * only and are left out. EGP, SAR and AED are not PayPal currencies at all.
     */
    public const array CURRENCIES = ['AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'ILS', 'MXN', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD', 'HUF', 'JPY', 'TWD'];

    public const array NEVER = ['EGP', 'SAR', 'AED'];

    private const array ZERO_DECIMAL = ['HUF', 'JPY', 'TWD'];

    private const int TOKEN_EXPIRY_MARGIN_SECONDS = 300;

    public function capabilities(): GatewayCapabilities
    {
        return new GatewayCapabilities(refunds: true, partialRefunds: true, capture: true, webhooks: true, refundIdempotency: true);
    }

    public function supportsCurrency(string $currency): bool
    {
        $currency = strtoupper($currency);

        return ! in_array($currency, self::NEVER, true) && in_array($currency, self::CURRENCIES, true);
    }

    protected function defaultExponent(string $currency): int
    {
        return in_array($currency, self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    public function toGatewayAmount(Money $money): int|string
    {
        return $this->toDecimalString($money);
    }

    public function createPayment(CreatePaymentRequest $request): CreatePaymentResult
    {
        if (! $this->supportsCurrency($request->currency)) {
            throw new GatewayNotConfigured("PayPal cannot charge {$request->currency}.");
        }

        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $request->order->public_id,
                'custom_id' => $request->payment->public_id,
                'invoice_id' => $request->order->order_number.'-'.$request->payment->attempt_no,
                'description' => mb_substr($request->description, 0, 127),
                'amount' => ['currency_code' => strtoupper($request->currency), 'value' => (string) $request->gatewayAmount],
            ]],
        ];

        $response = $this->api('orders.create', 'POST', '/v2/checkout/orders', $body, ['PayPal-Request-Id' => $request->idempotencyKey, 'Prefer' => 'return=representation']);
        $data = $response->json();

        $approve = collect($data['links'] ?? [])->first(static fn (array $link): bool => in_array($link['rel'] ?? null, ['approve', 'payer-action'], true));

        return new CreatePaymentResult(
            providerPaymentId: (string) $data['id'],
            status: ProviderPaymentStatus::RequiresAction,
            next: array_filter(['type' => 'paypal', 'provider_order_id' => (string) $data['id'], 'approve_url' => $approve['href'] ?? null]),
        );
    }

    public function capture(Payment $payment): VerifiedPaymentState
    {
        try {
            $response = $this->api('orders.capture', 'POST', '/v2/checkout/orders/'.rawurlencode((string) $payment->provider_payment_id).'/capture', null, [
                'PayPal-Request-Id' => 'pay_capture:'.$payment->public_id,
                'Prefer' => 'return=representation',
            ]);
        } catch (GatewayRequestFailed $exception) {
            if ($exception->providerCode === 'ORDER_ALREADY_CAPTURED') {
                return $this->fetchState($payment);
            }

            throw $exception;
        }

        return $this->orderState($response->json());
    }

    public function fetchState(Payment $payment): VerifiedPaymentState
    {
        try {
            $response = $this->api('orders.get', 'GET', '/v2/checkout/orders/'.rawurlencode((string) $payment->provider_payment_id));
        } catch (GatewayRequestFailed $exception) {
            if ($exception->httpStatus === 404) {
                return new VerifiedPaymentState(ProviderPaymentStatus::NotFound);
            }

            throw $exception;
        }

        return $this->orderState($response->json());
    }

    public function verifyWebhook(WebhookRequestData $request): VerifiedWebhook
    {
        $headers = [];

        foreach (['paypal-transmission-id', 'paypal-transmission-time', 'paypal-cert-url', 'paypal-auth-algo', 'paypal-transmission-sig'] as $name) {
            $headers[$name] = $request->header($name) ?? throw new InvalidWebhookSignature('missing_signature');
        }

        $event = json_decode($request->rawBody, true, 512, JSON_BIGINT_AS_STRING);

        if (! is_array($event) || ! isset($event['id'], $event['event_type'])) {
            throw new InvalidWebhookSignature('malformed_payload');
        }

        $webhookId = $this->credential('webhook_id');

        $envelope = '{'
            .'"auth_algo":'.json_encode($headers['paypal-auth-algo'], JSON_UNESCAPED_SLASHES)
            .',"cert_url":'.json_encode($headers['paypal-cert-url'], JSON_UNESCAPED_SLASHES)
            .',"transmission_id":'.json_encode($headers['paypal-transmission-id'], JSON_UNESCAPED_SLASHES)
            .',"transmission_sig":'.json_encode($headers['paypal-transmission-sig'], JSON_UNESCAPED_SLASHES)
            .',"transmission_time":'.json_encode($headers['paypal-transmission-time'], JSON_UNESCAPED_SLASHES)
            .',"webhook_id":'.json_encode($webhookId, JSON_UNESCAPED_SLASHES)
            .',"webhook_event":'.$request->rawBody
            .'}';

        $response = $this->api('webhooks.verify', 'POST', '/v1/notifications/verify-webhook-signature', $envelope);

        if (($response->json('verification_status') ?? null) !== 'SUCCESS') {
            throw new InvalidWebhookSignature('signature_mismatch');
        }

        return new VerifiedWebhook((string) $event['id'], (string) $event['event_type'], $event);
    }

    public function parseWebhook(VerifiedWebhook $webhook): GatewayEvent
    {
        $resource = $webhook->payload['resource'] ?? [];
        $type = $webhook->eventType;

        return match (true) {
            in_array($type, ['CHECKOUT.ORDER.APPROVED', 'CHECKOUT.PAYMENT-APPROVAL.REVERSED'], true) => new GatewayEvent(GatewayEventKind::Payment, providerPaymentId: $resource['id'] ?? null),
            in_array($type, ['PAYMENT.CAPTURE.COMPLETED', 'PAYMENT.CAPTURE.PENDING', 'PAYMENT.CAPTURE.DECLINED', 'PAYMENT.CAPTURE.REVERSED'], true) => new GatewayEvent(
                GatewayEventKind::Payment,
                providerPaymentId: $resource['supplementary_data']['related_ids']['order_id'] ?? null,
                providerCaptureId: $type === 'PAYMENT.CAPTURE.REVERSED' ? $this->upCaptureId($resource) : ($resource['id'] ?? null),
            ),
            in_array($type, ['PAYMENT.CAPTURE.REFUNDED', 'PAYMENT.REFUND.PENDING', 'PAYMENT.REFUND.FAILED'], true) => new GatewayEvent(
                GatewayEventKind::Refund,
                providerCaptureId: $this->upCaptureId($resource),
                providerRefundId: $resource['id'] ?? null,
            ),
            str_starts_with($type, 'CUSTOMER.DISPUTE.') => new GatewayEvent(
                GatewayEventKind::Dispute,
                providerCaptureId: $resource['disputed_transactions'][0]['seller_transaction_id'] ?? null,
                dispute: $this->dispute($resource),
            ),
            default => GatewayEvent::ignored(),
        };
    }

    public function refund(Refund $refund, Payment $payment, int|string $gatewayAmount): RefundResult
    {
        $capture = $payment->provider_capture_id ?? throw new GatewayNotConfigured('The payment has no PayPal capture to refund.');

        $response = $this->api('captures.refund', 'POST', '/v2/payments/captures/'.rawurlencode($capture).'/refund', [
            'amount' => ['currency_code' => $payment->currency, 'value' => (string) $gatewayAmount],
            'custom_id' => $refund->public_id,
        ], ['PayPal-Request-Id' => 'refund:'.$refund->public_id, 'Prefer' => 'return=representation']);

        return $this->refundResult($response->json());
    }

    /**
     * By id; a lost create is re-sent with the same PayPal-Request-Id, which PayPal answers
     * with the original result while it retains the key (45 days documented for refunds; the
     * caller limits this to the configured window).
     */
    public function fetchRefund(Refund $refund, Payment $payment): RefundResult
    {
        if ($refund->provider_refund_id === null) {
            return $this->refund($refund, $payment, $this->toDecimalString(Money::of($refund->amount, $refund->currency)));
        }

        return $this->refundResult($this->api('refunds.get', 'GET', '/v2/payments/refunds/'.rawurlencode($refund->provider_refund_id))->json());
    }

    /**
     * @param  array<string, mixed>  $order
     */
    private function orderState(array $order): VerifiedPaymentState
    {
        $orderStatus = (string) ($order['status'] ?? '');
        $capture = $order['purchase_units'][0]['payments']['captures'][0] ?? null;
        $raw = ['id' => $order['id'] ?? null, 'status' => $orderStatus, 'capture_status' => $capture['status'] ?? null];

        if (! is_array($capture)) {
            $status = match ($orderStatus) {
                'APPROVED' => ProviderPaymentStatus::Approved,
                'VOIDED' => ProviderPaymentStatus::Cancelled,
                'PAYER_ACTION_REQUIRED' => ProviderPaymentStatus::RequiresAction,
                default => ProviderPaymentStatus::Pending,
            };

            return new VerifiedPaymentState($status, raw: $raw);
        }

        $status = match ((string) ($capture['status'] ?? '')) {
            'COMPLETED', 'PARTIALLY_REFUNDED', 'REFUNDED' => ProviderPaymentStatus::Succeeded,
            'PENDING' => ProviderPaymentStatus::Processing,
            'DECLINED', 'FAILED' => ProviderPaymentStatus::Failed,
            default => ProviderPaymentStatus::Pending,
        };

        $amount = $capture['amount'] ?? null;
        $fee = $capture['seller_receivable_breakdown']['paypal_fee'] ?? null;

        return new VerifiedPaymentState(
            status: $status,
            amount: is_array($amount) ? Money::of((string) $amount['value'], (string) $amount['currency_code']) : null,
            providerCaptureId: isset($capture['id']) ? (string) $capture['id'] : null,
            fee: is_array($fee) ? Money::of((string) $fee['value'], (string) $fee['currency_code']) : null,
            failureCode: $status === ProviderPaymentStatus::Failed ? strtolower((string) $capture['status']) : null,
            raw: $raw,
        );
    }

    /**
     * @param  array<string, mixed>  $refund
     */
    private function refundResult(array $refund): RefundResult
    {
        $status = match ((string) ($refund['status'] ?? '')) {
            'COMPLETED' => RefundStatus::Succeeded,
            'CANCELLED', 'FAILED' => RefundStatus::Failed,
            default => RefundStatus::Pending,
        };
        $amount = $refund['amount'] ?? null;

        return new RefundResult(
            status: $status,
            providerRefundId: isset($refund['id']) ? (string) $refund['id'] : null,
            amount: is_array($amount) ? Money::of((string) $amount['value'], (string) $amount['currency_code']) : null,
            failureReason: $status === RefundStatus::Failed ? ($refund['status_details']['reason'] ?? strtolower((string) $refund['status'])) : null,
        );
    }

    /**
     * The capture a refund belongs to, from its `up` link (UNVERIFIED for every event shape).
     *
     * @param  array<string, mixed>  $resource
     */
    private function upCaptureId(array $resource): ?string
    {
        foreach ($resource['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'up' && preg_match('#/captures/([^/?]+)#', (string) ($link['href'] ?? ''), $match) === 1) {
                return $match[1];
            }
        }

        return null;
    }

    /**
     * Dispute fields per PayPal's dispute resource; the status mapping is UNVERIFIED.
     *
     * @param  array<string, mixed>  $resource
     */
    private function dispute(array $resource): ?DisputeData
    {
        $amount = $resource['dispute_amount'] ?? null;

        if (! isset($resource['dispute_id']) || ! is_array($amount)) {
            return null;
        }

        $status = match (true) {
            ($resource['status'] ?? null) !== 'RESOLVED' => PaymentDisputeStatus::Open,
            ($resource['dispute_outcome']['outcome_code'] ?? null) === 'RESOLVED_SELLER_FAVOUR' => PaymentDisputeStatus::Won,
            ($resource['dispute_outcome']['outcome_code'] ?? null) === 'RESOLVED_BUYER_FAVOUR' => PaymentDisputeStatus::Lost,
            default => PaymentDisputeStatus::Closed,
        };

        try {
            $due = isset($resource['seller_response_due_date']) ? CarbonImmutable::parse((string) $resource['seller_response_due_date']) : null;
        } catch (Throwable) {
            $due = null;
        }

        return new DisputeData((string) $resource['dispute_id'], $status, $resource['reason'] ?? null, Money::of((string) $amount['value'], (string) $amount['currency_code']), $due);
    }

    /**
     * @param  array<string, mixed>|string|null  $body
     * @param  array<string, string>  $headers
     *
     * @throws GatewayRequestFailed
     */
    private function api(string $operation, string $method, string $path, array|string|null $body = null, array $headers = []): Response
    {
        $request = $this->http()->withToken($this->token())->withHeaders($headers);

        if (is_string($body)) {
            $request = $request->withBody($body, 'application/json');
            $body = null;
        } elseif ($body !== null) {
            $request = $request->asJson();
        }

        $response = $this->sendLogged($operation, $request, $method, $this->baseUrl().$path, $body === null ? null : ['json' => $body]);

        if (! $response->successful()) {
            throw $this->failure($operation, $response, $response->json('details.0.issue') ?? $response->json('name'));
        }

        return $response;
    }

    private function baseUrl(): string
    {
        return (string) config('payments.paypal.base_urls.'.($this->isProduction() ? 'production' : 'sandbox'));
    }

    /**
     * OAuth client-credentials token, cached encrypted until shortly before `expires_in`.
     *
     * @throws GatewayRequestFailed
     * @throws GatewayNotConfigured
     */
    private function token(): string
    {
        $clientId = $this->credential('client_id');
        $cacheKey = sprintf('payments:paypal:%d:%s:%s', $this->account->id, $this->account->mode->value, hash('sha256', $clientId));
        $cached = Cache::get($cacheKey);

        if (is_string($cached)) {
            try {
                return Crypt::decryptString($cached);
            } catch (Throwable) {
                Cache::forget($cacheKey);
            }
        }

        $request = $this->http()->asForm()->withBasicAuth($clientId, $this->credential('client_secret'));
        $response = $this->sendLogged('oauth.token', $request, 'POST', $this->baseUrl().'/v1/oauth2/token', ['form_params' => ['grant_type' => 'client_credentials']]);

        $token = $response->json('access_token');

        if (! $response->successful() || ! is_string($token)) {
            throw $this->failure('oauth.token', $response);
        }

        $ttl = max(60, (int) $response->json('expires_in', 3600) - self::TOKEN_EXPIRY_MARGIN_SECONDS);
        Cache::put($cacheKey, Crypt::encryptString($token), $ttl);

        return $token;
    }
}
