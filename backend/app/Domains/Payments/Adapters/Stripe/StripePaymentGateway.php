<?php

namespace App\Domains\Payments\Adapters\Stripe;

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
use Carbon\CarbonImmutable;
use Closure;
use Stripe\ApiRequestor;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Stripe PaymentIntents (INTEGRATIONS.md 2) through the official SDK. Status: mocked tests only,
 * awaiting sandbox verification; BD1 (merchant entity) must be settled before go-live.
 *
 * - Create `POST /v1/payment_intents` with `automatic_payment_methods[enabled]=true`, so the
 *   methods are whatever the account enables; none are hardcoded. Key `pay_create:{public_id}`.
 *   The intent is confirmed by the client, which passes its own `return_url`; for an `app`
 *   attempt the server's value is published as `next.return_url` for PaymentSheet (M-A6).
 * - Verify `GET /v1/payment_intents/{id}` (status, `amount_received`, `currency`), with
 *   `latest_charge` expanded for card country only. The provider fee is not read: the
 *   balance-transaction expansion is UNVERIFIED, so fees are estimated from fee rules.
 * - Webhooks: `Stripe-Signature` checked by the SDK over the raw body, 5-minute tolerance,
 *   multiple `v1` signatures during secret rotation.
 * - Refunds `POST /v1/refunds` (partial allowed) with key `refund:{public_id}`.
 */
class StripePaymentGateway extends AbstractPaymentGateway
{
    /**
     * Zero-decimal currencies listed by Stripe (`/currencies`). Stripe's special cases (ISK,
     * UGX sent with a 00 suffix; HUF, TWD payout quirks) stay disabled until verified.
     */
    private const array ZERO_DECIMAL = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

    private const array UNVERIFIED_SPECIAL_CASES = ['ISK', 'UGX', 'HUF', 'TWD'];

    private ?StripeClient $client = null;

    public function capabilities(): GatewayCapabilities
    {
        return new GatewayCapabilities(refunds: true, partialRefunds: true, capture: false, webhooks: true, refundIdempotency: true);
    }

    /**
     * Presentment currencies depend on the account; the merchant list decides. Only Stripe's
     * special cases are refused outright until verified (EGP and SAR also stay disabled in
     * `payment_gateway_currencies` until confirmed on the account, INTEGRATIONS.md 2).
     */
    public function supportsCurrency(string $currency): bool
    {
        return preg_match('/^[A-Z]{3}$/', strtoupper($currency)) === 1
            && ! in_array(strtoupper($currency), self::UNVERIFIED_SPECIAL_CASES, true);
    }

    protected function defaultExponent(string $currency): int
    {
        return in_array($currency, self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    public function createPayment(CreatePaymentRequest $request): CreatePaymentResult
    {
        $intent = $this->call('payment_intents.create', fn (StripeClient $client) => $client->paymentIntents->create([
            'amount' => (int) $request->gatewayAmount,
            'currency' => strtolower($request->currency),
            'automatic_payment_methods' => ['enabled' => true],
            'description' => $request->description,
            'receipt_email' => $request->customerEmail,
            'metadata' => [
                'order_public_id' => $request->order->public_id,
                'payment_public_id' => $request->payment->public_id,
            ],
        ], ['idempotency_key' => $request->idempotencyKey]));

        return new CreatePaymentResult(
            providerPaymentId: (string) $intent->id,
            status: $this->mapIntentStatus((string) $intent->status, $intent->last_payment_error ?? null),
            next: array_filter(['type' => 'stripe', 'client_secret' => (string) $intent->client_secret, 'return_url' => $request->sdkReturnUrl]),
        );
    }

    public function fetchState(Payment $payment): VerifiedPaymentState
    {
        $intent = $this->call('payment_intents.retrieve', fn (StripeClient $client) => $client->paymentIntents->retrieve(
            (string) $payment->provider_payment_id,
            ['expand' => ['latest_charge']],
        ));

        $status = $this->mapIntentStatus((string) $intent->status, $intent->last_payment_error ?? null);
        $charge = is_object($intent->latest_charge ?? null) ? $intent->latest_charge : null;
        $card = $charge?->payment_method_details?->card ?? null;
        $currency = strtoupper((string) $intent->currency);

        return new VerifiedPaymentState(
            status: $status,
            amount: $status === ProviderPaymentStatus::Succeeded ? $this->fromMinorUnits((int) $intent->amount_received, $currency) : null,
            providerCaptureId: $charge?->id,
            refundedAmount: $charge !== null && (int) ($charge->amount_refunded ?? 0) > 0 ? $this->fromMinorUnits((int) $charge->amount_refunded, $currency) : null,
            cardCountry: is_string($card?->country ?? null) ? strtoupper($card->country) : null,
            cardFingerprint: is_string($card?->fingerprint ?? null) ? $card->fingerprint : null,
            failureCode: $intent->last_payment_error?->code ?? null,
            failureMessage: $intent->last_payment_error?->message ?? null,
            raw: ['id' => $intent->id, 'status' => $intent->status, 'amount_received' => $intent->amount_received, 'currency' => $intent->currency],
        );
    }

    public function verifyWebhook(WebhookRequestData $request): VerifiedWebhook
    {
        $signature = $request->header('Stripe-Signature') ?? throw new InvalidWebhookSignature('missing_signature');

        try {
            $event = Webhook::constructEvent($request->rawBody, $signature, $this->webhookSecret(), (int) config('payments.stripe.webhook_tolerance_seconds'));
        } catch (SignatureVerificationException) {
            throw new InvalidWebhookSignature('signature_mismatch');
        } catch (UnexpectedValueException) {
            throw new InvalidWebhookSignature('malformed_payload');
        }

        return new VerifiedWebhook((string) $event->id, (string) $event->type, $event->toArray());
    }

    public function parseWebhook(VerifiedWebhook $webhook): GatewayEvent
    {
        $object = $webhook->payload['data']['object'] ?? [];
        $type = $webhook->eventType;

        return match (true) {
            str_starts_with($type, 'payment_intent.') => new GatewayEvent(GatewayEventKind::Payment, providerPaymentId: $object['id'] ?? null),
            $type === 'charge.refunded' => new GatewayEvent(GatewayEventKind::Refund, providerPaymentId: $object['payment_intent'] ?? null),
            str_starts_with($type, 'refund.') => new GatewayEvent(GatewayEventKind::Refund, providerPaymentId: $object['payment_intent'] ?? null, providerRefundId: $object['id'] ?? null),
            str_starts_with($type, 'charge.dispute.') => new GatewayEvent(GatewayEventKind::Dispute, providerPaymentId: $object['payment_intent'] ?? null, dispute: $this->dispute($object)),
            default => GatewayEvent::ignored(),
        };
    }

    public function refund(Refund $refund, Payment $payment, int|string $gatewayAmount): RefundResult
    {
        $created = $this->call('refunds.create', fn (StripeClient $client) => $client->refunds->create([
            'payment_intent' => (string) $payment->provider_payment_id,
            'amount' => (int) $gatewayAmount,
            'metadata' => ['refund_public_id' => $refund->public_id],
        ], ['idempotency_key' => 'refund:'.$refund->public_id]));

        return $this->refundResult($created);
    }

    /**
     * By id; or, when the create response was lost, by our public id in the refund metadata.
     */
    public function fetchRefund(Refund $refund, Payment $payment): RefundResult
    {
        if ($refund->provider_refund_id !== null) {
            return $this->refundResult($this->call('refunds.retrieve', fn (StripeClient $client) => $client->refunds->retrieve($refund->provider_refund_id)));
        }

        $list = $this->call('refunds.list', fn (StripeClient $client) => $client->refunds->all(['payment_intent' => (string) $payment->provider_payment_id, 'limit' => 100]));

        foreach ($list->data as $candidate) {
            if (($candidate->metadata['refund_public_id'] ?? null) === $refund->public_id) {
                return $this->refundResult($candidate);
            }
        }

        return new RefundResult(RefundStatus::Requested);
    }

    private function refundResult(object $refund): RefundResult
    {
        $status = match ((string) $refund->status) {
            'succeeded' => RefundStatus::Succeeded,
            'failed', 'canceled' => RefundStatus::Failed,
            default => RefundStatus::Pending,
        };

        return new RefundResult(
            status: $status,
            providerRefundId: (string) $refund->id,
            amount: $this->fromMinorUnits((int) $refund->amount, strtoupper((string) $refund->currency)),
            failureReason: $status === RefundStatus::Failed ? ($refund->failure_reason ?? (string) $refund->status) : null,
        );
    }

    private function mapIntentStatus(string $status, mixed $lastError): ProviderPaymentStatus
    {
        return match ($status) {
            'succeeded' => ProviderPaymentStatus::Succeeded,
            'processing', 'requires_capture' => ProviderPaymentStatus::Processing,
            'requires_action', 'requires_confirmation' => ProviderPaymentStatus::RequiresAction,
            'canceled' => ProviderPaymentStatus::Cancelled,
            'requires_payment_method' => $lastError !== null ? ProviderPaymentStatus::Failed : ProviderPaymentStatus::Pending,
            default => ProviderPaymentStatus::Pending,
        };
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function dispute(array $object): ?DisputeData
    {
        if (! isset($object['id'], $object['amount'], $object['currency'])) {
            return null;
        }

        $status = match ((string) ($object['status'] ?? '')) {
            'won' => PaymentDisputeStatus::Won,
            'lost' => PaymentDisputeStatus::Lost,
            'warning_closed' => PaymentDisputeStatus::Closed,
            default => PaymentDisputeStatus::Open,
        };
        $due = $object['evidence_details']['due_by'] ?? null;

        return new DisputeData(
            (string) $object['id'],
            $status,
            isset($object['reason']) ? (string) $object['reason'] : null,
            $this->fromMinorUnits((int) $object['amount'], strtoupper((string) $object['currency'])),
            is_int($due) ? CarbonImmutable::createFromTimestamp($due) : null,
        );
    }

    /**
     * @template T
     *
     * @param  Closure(StripeClient): T  $operation
     * @return T
     *
     * @throws GatewayRequestFailed
     */
    private function call(string $name, Closure $operation): mixed
    {
        $started = hrtime(true);

        try {
            $result = $operation($this->client());
            $this->log($name, 'stripe', 'https://api.stripe.com/'.$name, 200, $started, null);

            return $result;
        } catch (ApiConnectionException $exception) {
            $this->log($name, 'stripe', 'https://api.stripe.com/'.$name, null, $started, 'connection');

            throw new GatewayRequestFailed("stripe {$name}: connection", ambiguous: ! str_starts_with($exception->getMessage(), LaravelStripeHttpClient::NOT_SENT_PREFIX));
        } catch (ApiErrorException $exception) {
            $status = $exception->getHttpStatus();
            $this->log($name, 'stripe', 'https://api.stripe.com/'.$name, $status, $started, $exception->getStripeCode());

            throw new GatewayRequestFailed("stripe {$name}: HTTP ".($status ?? 'unknown'), ambiguous: $status === null || $status >= 500 || $status === 429, httpStatus: $status, providerCode: $exception->getStripeCode());
        }
    }

    /**
     * @throws GatewayNotConfigured
     */
    private function client(): StripeClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $secret = $this->credential('secret_key');
        $expectedPrefix = $this->isProduction() ? 'sk_live_' : 'sk_test_';

        if (! str_starts_with($secret, $expectedPrefix) && ! str_starts_with($secret, $this->isProduction() ? 'rk_live_' : 'rk_test_')) {
            throw new GatewayNotConfigured("Stripe key does not match {$this->account->mode->value} mode.");
        }

        ApiRequestor::setHttpClient(new LaravelStripeHttpClient((int) config('payments.http.connect_timeout'), (int) config('payments.http.timeout')));

        return $this->client = new StripeClient([
            'api_key' => $secret,
            'stripe_version' => (string) config('payments.stripe.api_version'),
            'max_network_retries' => 0,
        ]);
    }
}
