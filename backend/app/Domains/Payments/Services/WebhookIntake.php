<?php

namespace App\Domains\Payments\Services;

use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\InvalidWebhookSignature;
use App\Domains\Payments\Jobs\ProcessPaymentWebhook;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Webhook steps 1 to 3 (architecture 9.4): verify the signature over the raw body, store the
 * event once (unique `uq_pwh` on gateway and provider event id, payload encrypted), and queue
 * processing. A duplicate is acknowledged without being processed again. A bad signature is not
 * stored; it is recorded as a security event with the reason only.
 */
class WebhookIntake
{
    /**
     * Header names kept with the event, for diagnosis. Signature values are not kept.
     */
    private const array KEPT_HEADERS = ['content-type', 'user-agent', 'paypal-transmission-id', 'paypal-transmission-time', 'paypal-auth-algo', 'paypal-cert-url'];

    public function __construct(private PaymentGatewayRegistry $registry, private SecurityEventRecorder $security) {}

    /**
     * @return array{stored: bool, duplicate: bool}
     *
     * @throws InvalidWebhookSignature
     * @throws ModelNotFoundException for an unknown gateway
     */
    public function receive(string $gatewayKey, WebhookRequestData $request, ?string $ip): array
    {
        $account = PaymentGateway::query()->where('key', $gatewayKey)->firstOrFail();

        try {
            $verified = $this->registry->for($account)->verifyWebhook($request);
        } catch (InvalidWebhookSignature|GatewayNotConfigured $exception) {
            $this->security->record('payments.webhook_signature_invalid', SecurityEventSeverity::Warning, $ip, context: [
                'gateway' => $account->key,
                'reason' => $exception instanceof InvalidWebhookSignature ? $exception->getMessage() : 'not_configured',
            ]);

            throw $exception instanceof InvalidWebhookSignature ? $exception : new InvalidWebhookSignature('not_configured');
        }

        try {
            $webhook = (new PaymentWebhook)->forceFill([
                'payment_gateway_id' => $account->id,
                'provider_event_id' => mb_substr($verified->eventId, 0, 128),
                'event_type' => mb_substr($verified->eventType, 0, 96),
                'signature_valid' => true,
                'headers' => array_intersect_key($request->headers, array_flip(self::KEPT_HEADERS)),
                'payload' => $request->rawBody,
                'status' => WebhookStatus::Received,
            ]);
            $webhook->save();
        } catch (UniqueConstraintViolationException) {
            return ['stored' => false, 'duplicate' => true];
        }

        ProcessPaymentWebhook::dispatch($webhook->id);

        return ['stored' => true, 'duplicate' => false];
    }
}
