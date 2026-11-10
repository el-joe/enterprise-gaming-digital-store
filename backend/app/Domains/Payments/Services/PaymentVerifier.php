<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Models\Payment;
use Illuminate\Support\Facades\Log;

/**
 * Server-side status check: read the payment from the provider (no transaction held), capture
 * it if the provider says it is approved and capture is ours to do (PayPal), then apply the
 * result. Webhooks, the browser's return, the PayPal capture endpoint and reconciliation all
 * come through here, so whichever arrives first, the outcome is the same and applied once.
 *
 * A provider failure changes nothing: the payment is scheduled for another check.
 */
class PaymentVerifier
{
    public function __construct(private PaymentGatewayRegistry $registry, private PaymentStateApplier $applier) {}

    public function verify(Payment $payment, OrderActor $actor, bool $captureIfApproved = true): ?Payment
    {
        if ($payment->provider_payment_id === null) {
            return null;
        }

        try {
            $adapter = $this->registry->forPayment($payment);
            $state = $adapter->fetchState($payment);

            if ($captureIfApproved && $state->status === ProviderPaymentStatus::Approved && $adapter->capabilities()->capture) {
                $state = $adapter->capture($payment);
            }
        } catch (GatewayRequestFailed|GatewayNotConfigured $exception) {
            $this->scheduleRetry($payment, $exception->getMessage());

            return null;
        }

        return $this->applier->apply($payment->id, $state, $actor);
    }

    private function scheduleRetry(Payment $payment, string $reason): void
    {
        $backoff = config('payments.reconcile_backoff_minutes');
        $checks = $payment->verified_at === null ? 0 : 1;
        $minutes = (int) ($backoff[min($checks, count($backoff) - 1)] ?? 5);

        Payment::query()->whereKey($payment->id)
            ->whereNotIn('status', [PaymentStatus::Succeeded, PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded, PaymentStatus::DuplicateCapture])
            ->update(['next_check_at' => now()->addMinutes($minutes)]);

        Log::warning('payments.verification_failed', ['payment_id' => $payment->id, 'reason' => $reason]);
    }
}
