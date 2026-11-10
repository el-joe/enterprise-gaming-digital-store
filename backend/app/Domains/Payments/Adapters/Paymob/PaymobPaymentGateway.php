<?php

namespace App\Domains\Payments\Adapters\Paymob;

use App\Domains\Payments\Adapters\AbstractPaymentGateway;
use App\Domains\Payments\DTOs\CreatePaymentRequest;
use App\Domains\Payments\DTOs\CreatePaymentResult;
use App\Domains\Payments\DTOs\GatewayCapabilities;
use App\Domains\Payments\DTOs\GatewayEvent;
use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\DTOs\VerifiedWebhook;
use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Enums\GatewayEventKind;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Exceptions\InvalidWebhookSignature;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Paymob Egypt, Intention API and Unified Checkout (INTEGRATIONS.md 4). Status: mocked tests
 * only, awaiting sandbox verification. EGP only; offered only to eligible Egyptian customers
 * (eligibility rules plus the gateway's display-currency-only setting).
 *
 * - Create `POST /v1/intention/` with `Token {secret_key}`. Paymob documents no idempotency: a
 *   timed-out create is abandoned and a new attempt opened (`special_reference` =
 *   `{order_number}-{attempt}`), since no customer was sent to it.
 * - Verify with the transaction (`GET /api/acceptance/transactions/{id}`) or, before a callback
 *   gave us its id, `transaction_inquiry` by Paymob order id (most recent transaction only).
 * - Callbacks: HMAC-SHA512 over 20 fields (PaymobHmac); the `hmac` value is read from the query
 *   string, then the body (its location is UNVERIFIED). There is no event id, so the event id is
 *   a hash of the state fields (architecture 9.4).
 * - Refunds `POST /api/acceptance/void_refund/refund`, partial allowed, one in flight per
 *   payment (no idempotency); a lost refund is resolved by re-reading `refunded_amount_cents`.
 * - `merchant_commission` is not used as the fee (UNVERIFIED); fees come from fee rules.
 */
class PaymobPaymentGateway extends AbstractPaymentGateway
{
    private const array EVENT_ID_FIELDS = ['id', 'success', 'pending', 'is_refunded', 'is_voided', 'is_capture', 'refunded_amount_cents', 'captured_amount', 'updated_at'];

    public function capabilities(): GatewayCapabilities
    {
        return new GatewayCapabilities(refunds: true, partialRefunds: true, capture: false, webhooks: true, refundIdempotency: false, singleRefundInFlight: true);
    }

    public function supportsCurrency(string $currency): bool
    {
        return strtoupper($currency) === 'EGP';
    }

    public function createPayment(CreatePaymentRequest $request): CreatePaymentResult
    {
        if (! $this->supportsCurrency($request->currency)) {
            throw new GatewayNotConfigured("Paymob cannot charge {$request->currency}.");
        }

        $integrations = array_values(array_map('intval', array_filter($request->integrationRefs, static fn (string $ref): bool => ctype_digit($ref))));

        if ($integrations === []) {
            throw new GatewayNotConfigured('Paymob has no integration id for this method.');
        }

        [$first, $last] = $this->names($request->customerName);
        $reference = $request->order->order_number.'-'.$request->payment->attempt_no;

        $response = $this->api('intention.create', 'POST', '/v1/intention/', [
            'amount' => (int) $request->gatewayAmount,
            'currency' => 'EGP',
            'payment_methods' => $integrations,
            'items' => [['name' => mb_substr($request->description, 0, 50), 'amount' => (int) $request->gatewayAmount, 'description' => $request->order->order_number, 'quantity' => 1]],
            'billing_data' => [
                'first_name' => $first,
                'last_name' => $last,
                'email' => $request->customerEmail,
                'phone_number' => $request->customerPhone ?? 'NA',
            ],
            'special_reference' => $reference,
            'notification_url' => $request->notificationUrl,
            // The browser return for the attempt's channel (web page or app link, M-A6); display only.
            'redirection_url' => $request->returnUrl,
            'extras' => ['payment_public_id' => $request->payment->public_id],
        ], secretAuth: true);

        $data = $response->json();
        $clientSecret = (string) ($data['client_secret'] ?? '');

        if (! isset($data['id']) || $clientSecret === '') {
            throw new GatewayRequestFailed('paymob intention.create: unexpected response', ambiguous: true, httpStatus: $response->status());
        }

        return new CreatePaymentResult(
            providerPaymentId: (string) $data['id'],
            status: ProviderPaymentStatus::RequiresAction,
            next: [
                'type' => 'redirect',
                'redirect_url' => $this->baseUrl().'/unifiedcheckout/?'.http_build_query(['publicKey' => $this->credential('public_key'), 'clientSecret' => $clientSecret]),
            ],
            providerOrderId: isset($data['intention_order_id']) ? (string) $data['intention_order_id'] : null,
            providerReference: $reference,
        );
    }

    public function fetchState(Payment $payment): VerifiedPaymentState
    {
        $transaction = $this->transaction($payment);

        if ($transaction === null) {
            return new VerifiedPaymentState(ProviderPaymentStatus::Pending);
        }

        return $this->transactionState($transaction);
    }

    public function verifyWebhook(WebhookRequestData $request): VerifiedWebhook
    {
        $body = json_decode($request->rawBody, true, 512, JSON_BIGINT_AS_STRING);

        if (! is_array($body) || ! is_array($body['obj'] ?? null)) {
            throw new InvalidWebhookSignature('malformed_payload');
        }

        $provided = $request->query['hmac'] ?? $body['hmac'] ?? null;

        if (! is_string($provided) || $provided === '') {
            throw new InvalidWebhookSignature('missing_signature');
        }

        if (! PaymobHmac::verify($body['obj'], $provided, $this->webhookSecret())) {
            throw new InvalidWebhookSignature('signature_mismatch');
        }

        $parts = array_map(static fn (string $field): string => match ($value = $body['obj'][$field] ?? null) {
            true => 'true',
            false => 'false',
            null => '',
            default => is_array($value) ? '' : (string) $value,
        }, self::EVENT_ID_FIELDS);

        return new VerifiedWebhook(hash('sha256', implode('|', $parts)), (string) ($body['type'] ?? 'TRANSACTION'), $body);
    }

    public function parseWebhook(VerifiedWebhook $webhook): GatewayEvent
    {
        $transaction = $webhook->payload['obj'] ?? [];

        if ($webhook->eventType !== 'TRANSACTION' || ! isset($transaction['id'])) {
            return GatewayEvent::ignored();
        }

        $refunded = ($transaction['is_refunded'] ?? false) === true || (int) ($transaction['refunded_amount_cents'] ?? 0) > 0;

        return new GatewayEvent(
            $refunded ? GatewayEventKind::Refund : GatewayEventKind::Payment,
            providerOrderId: isset($transaction['order']['id']) ? (string) $transaction['order']['id'] : null,
            providerCaptureId: (string) $transaction['id'],
        );
    }

    public function refund(Refund $refund, Payment $payment, int|string $gatewayAmount): RefundResult
    {
        $transactionId = $payment->provider_capture_id ?? throw new GatewayNotConfigured('The payment has no Paymob transaction to refund.');

        $response = $this->api('refund', 'POST', '/api/acceptance/void_refund/refund', [
            'transaction_id' => $transactionId,
            'amount_cents' => (int) $gatewayAmount,
        ], secretAuth: true);

        $data = $response->json();

        $status = match (true) {
            ($data['pending'] ?? false) === true => RefundStatus::Pending,
            ($data['success'] ?? false) === true => RefundStatus::Succeeded,
            default => RefundStatus::Failed,
        };

        return new RefundResult(
            status: $status,
            providerRefundId: isset($data['id']) ? (string) $data['id'] : null,
            amount: $this->fromMinorUnits((int) $gatewayAmount, 'EGP'),
            failureReason: $status === RefundStatus::Failed ? 'declined' : null,
        );
    }

    /**
     * Paymob has no refund read: re-read the parent transaction. When its refunded total covers
     * every succeeded refund plus this one, this one went through; when it does not, it never
     * ran (`Requested`), and nothing is re-sent automatically.
     */
    public function fetchRefund(Refund $refund, Payment $payment): RefundResult
    {
        $transaction = $this->transaction($payment) ?? throw new GatewayRequestFailed('paymob refund read: transaction not found', ambiguous: true);
        $refundedCents = (int) ($transaction['refunded_amount_cents'] ?? 0);

        $alreadyCents = $this->toMinorUnits(Money::of(
            (string) $payment->refunds()->where('status', RefundStatus::Succeeded)->whereKeyNot($refund->id)->sum('amount'),
            'EGP',
        ));
        $thisCents = $this->toMinorUnits(Money::of($refund->amount, 'EGP'));

        if ($refundedCents >= $alreadyCents + $thisCents) {
            return new RefundResult(RefundStatus::Succeeded, $refund->provider_refund_id, Money::of($refund->amount, 'EGP'));
        }

        return new RefundResult(RefundStatus::Requested, $refund->provider_refund_id);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function transaction(Payment $payment): ?array
    {
        if ($payment->provider_capture_id !== null) {
            return $this->api('transactions.get', 'GET', '/api/acceptance/transactions/'.rawurlencode($payment->provider_capture_id), bearer: true)->json();
        }

        if ($payment->provider_order_id === null) {
            return null;
        }

        try {
            $data = $this->api('transaction_inquiry', 'POST', '/api/ecommerce/orders/transaction_inquiry', ['order_id' => $payment->provider_order_id], bearer: true)->json();
        } catch (GatewayRequestFailed $exception) {
            if ($exception->httpStatus === 404) {
                return null;
            }

            throw $exception;
        }

        return is_array($data) && isset($data['id']) ? $data : null;
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function transactionState(array $transaction): VerifiedPaymentState
    {
        $success = ($transaction['success'] ?? false) === true;
        $pending = ($transaction['pending'] ?? false) === true;
        $voided = ($transaction['is_voided'] ?? false) === true;
        $currency = strtoupper((string) ($transaction['currency'] ?? 'EGP'));

        $status = match (true) {
            $voided => ProviderPaymentStatus::Cancelled,
            $pending => ProviderPaymentStatus::Processing,
            $success => ProviderPaymentStatus::Succeeded,
            default => ProviderPaymentStatus::Failed,
        };

        $refundedCents = (int) ($transaction['refunded_amount_cents'] ?? 0);

        return new VerifiedPaymentState(
            status: $status,
            amount: $status === ProviderPaymentStatus::Succeeded ? $this->fromMinorUnits((int) $transaction['amount_cents'], $currency) : null,
            providerCaptureId: isset($transaction['id']) ? (string) $transaction['id'] : null,
            refundedAmount: $refundedCents > 0 ? $this->fromMinorUnits($refundedCents, $currency) : null,
            failureCode: $status === ProviderPaymentStatus::Failed ? 'declined' : null,
            raw: ['id' => $transaction['id'] ?? null, 'success' => $success, 'pending' => $pending, 'amount_cents' => $transaction['amount_cents'] ?? null, 'currency' => $currency],
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function names(?string $name): array
    {
        $parts = preg_split('/\s+/', trim((string) $name), 2) ?: [];

        return [($parts[0] ?? '') !== '' ? $parts[0] : 'NA', ($parts[1] ?? '') !== '' ? $parts[1] : 'NA'];
    }

    /**
     * @param  array<string, mixed>|null  $body
     *
     * @throws GatewayRequestFailed
     */
    private function api(string $operation, string $method, string $path, ?array $body = null, bool $secretAuth = false, bool $bearer = false): Response
    {
        $request = $this->http();

        if ($secretAuth) {
            $request = $request->withHeaders(['Authorization' => 'Token '.$this->credential('secret_key')]);
        }

        if ($bearer) {
            $request = $request->withToken($this->token());
        }

        $response = $this->sendLogged($operation, $body === null ? $request : $request->asJson(), $method, $this->baseUrl().$path, $body === null ? null : ['json' => $body]);

        if (! $response->successful()) {
            throw $this->failure($operation, $response);
        }

        return $response;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('payments.paymob.base_url'), '/');
    }

    /**
     * Auth token for the inquiry APIs (`POST /api/auth/tokens`, 60-minute life), cached
     * encrypted for slightly less.
     */
    private function token(): string
    {
        $apiKey = $this->credential('api_key');
        $cacheKey = sprintf('payments:paymob:%d:%s:%s', $this->account->id, $this->account->mode->value, hash('sha256', $apiKey));
        $cached = Cache::get($cacheKey);

        if (is_string($cached)) {
            try {
                return Crypt::decryptString($cached);
            } catch (Throwable) {
                Cache::forget($cacheKey);
            }
        }

        $response = $this->sendLogged('auth.token', $this->http()->asJson(), 'POST', $this->baseUrl().'/api/auth/tokens', ['json' => ['api_key' => $apiKey]]);
        $token = $response->json('token');

        if (! $response->successful() || ! is_string($token)) {
            throw $this->failure('auth.token', $response);
        }

        Cache::put($cacheKey, Crypt::encryptString($token), now()->addMinutes((int) config('payments.paymob.auth_token_minutes')));

        return $token;
    }
}
