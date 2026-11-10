<?php

namespace App\Domains\Payments\Services;

use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sends a refund to the provider exactly once, outside any transaction (architecture 9.3).
 * The refund is moved from `requested` to `pending` under its row lock first, so a repeated
 * job does nothing. A timeout leaves it `unknown`; `poll` then re-reads the provider before
 * anything is re-sent: Stripe and PayPal re-send with the same idempotency key inside the
 * retry window, Paymob never re-sends (no idempotency) and a refund it has no record of fails
 * for staff to retry as a new refund.
 */
class RefundExecutor
{
    public function __construct(private PaymentGatewayRegistry $registry, private RefundStateApplier $applier) {}

    public function execute(int $refundId): ?Refund
    {
        $claimed = DB::transaction(function () use ($refundId): ?Refund {
            $refund = Refund::query()->lockForUpdate()->find($refundId);

            if ($refund === null || $refund->status !== RefundStatus::Requested || $refund->payment_id === null) {
                return null;
            }

            $refund->forceFill(['status' => RefundStatus::Pending])->save();

            return $refund;
        });

        return $claimed === null ? null : $this->send($claimed);
    }

    /**
     * Resolve a `pending` or `unknown` refund from the provider.
     */
    public function poll(Refund $refund): Refund
    {
        $payment = Payment::query()->findOrFail($refund->payment_id);

        try {
            $result = $this->registry->forPayment($payment)->fetchRefund($refund, $payment);
        } catch (GatewayRequestFailed $exception) {
            Log::warning('payments.refund_poll_failed', ['refund_id' => $refund->id, 'error' => $exception->getMessage()]);

            return $refund;
        }

        if ($result->status !== RefundStatus::Requested) {
            return $this->applier->apply($refund->id, $result);
        }

        if ($refund->status === RefundStatus::Pending && $refund->provider_refund_id !== null) {
            return $refund;
        }

        $adapter = $this->registry->forPayment($payment);
        $withinWindow = $refund->created_at !== null && $refund->created_at->greaterThan(now()->subHours((int) config('payments.idempotency_retry_window_hours')));

        if ($adapter->capabilities()->refundIdempotency && $withinWindow) {
            return $this->send($refund);
        }

        if (! $adapter->capabilities()->refundIdempotency && $refund->created_at !== null && $refund->created_at->greaterThan(now()->subHours((int) config('payments.expire_unpaid_attempt_after_hours')))) {
            return $this->applier->apply($refund->id, new RefundResult(RefundStatus::Unknown, $refund->provider_refund_id));
        }

        return $this->applier->apply($refund->id, new RefundResult(RefundStatus::Failed, failureReason: 'not_found_at_provider'));
    }

    private function send(Refund $refund): Refund
    {
        $payment = Payment::query()->findOrFail($refund->payment_id);

        try {
            $adapter = $this->registry->forPayment($payment);
            $result = $adapter->refund($refund, $payment, $adapter->toGatewayAmount(Money::of($refund->amount, $refund->currency)));
        } catch (GatewayRequestFailed $exception) {
            Log::warning('payments.refund_request_failed', ['refund_id' => $refund->id, 'ambiguous' => $exception->ambiguous, 'error' => $exception->getMessage()]);

            $result = $exception->ambiguous
                ? new RefundResult(RefundStatus::Unknown)
                : new RefundResult(RefundStatus::Failed, failureReason: $exception->providerCode ?? 'refused');
        } catch (GatewayNotConfigured $exception) {
            $result = new RefundResult(RefundStatus::Failed, failureReason: 'gateway_unavailable');
        }

        return $this->applier->apply($refund->id, $result);
    }
}
