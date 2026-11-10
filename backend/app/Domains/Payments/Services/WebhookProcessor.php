<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Payments\DTOs\GatewayEvent;
use App\Domains\Payments\DTOs\VerifiedWebhook;
use App\Domains\Payments\Enums\GatewayEventKind;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Webhook steps a to c (architecture 9.4). The event only says which payment to look at: the
 * payment is found by provider reference, re-read from the provider, and the read is applied.
 * Handlers are state-based, so duplicates and any arrival order give the same result:
 * a refund event first re-verifies the payment (refund before capture), and a failure event
 * after success re-reads a success and changes nothing.
 */
class WebhookProcessor
{
    public function __construct(
        private PaymentGatewayRegistry $registry,
        private PaymentVerifier $verifier,
        private RefundExecutor $refunds,
        private DisputeRecorder $disputes,
    ) {}

    public function process(int $webhookId): void
    {
        $webhook = $this->claim($webhookId);

        if ($webhook === null) {
            return;
        }

        try {
            $account = PaymentGateway::query()->findOrFail($webhook->payment_gateway_id);
            $payload = json_decode((string) $webhook->payload, true, 512, JSON_BIGINT_AS_STRING);
            $event = $this->registry->for($account)->parseWebhook(new VerifiedWebhook($webhook->provider_event_id, $webhook->event_type, is_array($payload) ? $payload : []));

            if ($event->kind === GatewayEventKind::Ignored) {
                $this->finish($webhook, WebhookStatus::Ignored, null, 'event_not_used');

                return;
            }

            $payment = $this->locate($account, $event);

            if ($payment === null) {
                $this->finish($webhook, $this->exhausted($webhook) ? WebhookStatus::Ignored : WebhookStatus::Failed, null, 'payment_not_found');

                return;
            }

            $verified = true;

            if ($event->kind === GatewayEventKind::Dispute) {
                if ($event->dispute !== null) {
                    $this->disputes->record($payment, $event->dispute);
                }
            } else {
                $verified = $this->verifier->verify($payment, OrderActor::webhook()) !== null;
            }

            if ($event->kind === GatewayEventKind::Refund) {
                $this->syncRefunds($payment, $event->providerRefundId);
            }

            $this->finish($webhook, $verified ? WebhookStatus::Processed : WebhookStatus::Failed, $payment->id, $verified ? null : 'verification_failed');
        } catch (Throwable $exception) {
            report($exception);
            $this->finish($webhook, WebhookStatus::Failed, $webhook->payment_id, mb_substr(class_basename($exception), 0, 500));
        }
    }

    private function claim(int $webhookId): ?PaymentWebhook
    {
        return DB::transaction(function () use ($webhookId): ?PaymentWebhook {
            $webhook = PaymentWebhook::query()->lockForUpdate()->find($webhookId);

            if ($webhook === null || in_array($webhook->status, [WebhookStatus::Processed, WebhookStatus::Ignored], true)) {
                return null;
            }

            $webhook->forceFill(['attempts' => $webhook->attempts + 1])->save();

            return $webhook;
        });
    }

    private function locate(PaymentGateway $account, GatewayEvent $event): ?Payment
    {
        $query = static fn () => Payment::query()->where('payment_gateway_id', $account->id);

        return match (true) {
            $event->providerPaymentId !== null && ($found = $query()->where('provider_payment_id', $event->providerPaymentId)->first()) !== null => $found,
            $event->providerCaptureId !== null && ($found = $query()->where('provider_capture_id', $event->providerCaptureId)->first()) !== null => $found,
            $event->providerOrderId !== null => $query()->where('provider_order_id', $event->providerOrderId)->orderByDesc('attempt_no')->first(),
            default => null,
        };
    }

    private function syncRefunds(Payment $payment, ?string $providerRefundId): void
    {
        $open = Refund::query()->where('payment_id', $payment->id)->whereIn('status', [RefundStatus::Pending, RefundStatus::Unknown])->get();

        foreach ($open as $refund) {
            $this->refunds->poll($refund);
        }

        if ($providerRefundId !== null && ! Refund::query()->where('payment_id', $payment->id)->where('provider_refund_id', $providerRefundId)->exists() && $open->isEmpty()) {
            Log::alert('payments.refund_not_initiated_here', ['payment_id' => $payment->id]);
        }
    }

    private function exhausted(PaymentWebhook $webhook): bool
    {
        return $webhook->attempts >= (int) config('payments.webhook_max_attempts');
    }

    private function finish(PaymentWebhook $webhook, WebhookStatus $status, ?int $paymentId, ?string $error): void
    {
        $webhook->forceFill([
            'status' => $status,
            'payment_id' => $paymentId ?? $webhook->payment_id,
            'error' => $error,
            'processed_at' => in_array($status, [WebhookStatus::Processed, WebhookStatus::Ignored], true) ? now() : null,
        ])->save();
    }
}
