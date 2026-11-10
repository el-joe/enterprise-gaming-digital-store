<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Enums\ReconciliationItemType;
use App\Domains\Payments\Enums\ReconciliationRunKind;
use App\Domains\Payments\Enums\ReconciliationRunStatus;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\ReconciliationRun;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Architecture 9.5. `reconcileOpen` covers lost webhooks and lost jobs: open attempts are
 * re-read through the normal path, refunds are sent or re-read, failed webhooks re-processed.
 * `reconcileDaily` compares settled payments with the provider and records mismatches for
 * staff; it never changes state itself.
 */
class PaymentReconciler
{
    private const array OPEN = [PaymentStatus::Created, PaymentStatus::RequiresAction, PaymentStatus::Processing];

    private const int BATCH = 200;

    public function __construct(
        private PaymentVerifier $verifier,
        private RefundExecutor $refunds,
        private WebhookProcessor $webhooks,
        private PaymentGatewayRegistry $registry,
    ) {}

    /**
     * @return array{payments: int, expired: int, refunds: int, webhooks: int}
     */
    public function reconcileOpen(): array
    {
        $cutoff = now()->subMinutes((int) config('payments.reconcile_open_after_minutes'));
        $counts = ['payments' => 0, 'expired' => 0, 'refunds' => 0, 'webhooks' => 0];

        $due = Payment::query()->whereIn('status', self::OPEN)->where('created_at', '<=', $cutoff)
            ->where(static fn ($query) => $query->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
            ->orderBy('id')->limit(self::BATCH)->get();

        foreach ($due as $payment) {
            if ($payment->provider_payment_id !== null) {
                $this->verifier->verify($payment, OrderActor::system());
                $counts['payments']++;
            }

            $counts['expired'] += $this->expireIfAbandoned($payment->fresh());
        }

        $refunds = Refund::query()->whereIn('status', [RefundStatus::Requested, RefundStatus::Pending, RefundStatus::Unknown])
            ->where('updated_at', '<=', $cutoff)->orderBy('id')->limit(self::BATCH)->get();

        foreach ($refunds as $refund) {
            $refund->status === RefundStatus::Requested ? $this->refunds->execute($refund->id) : $this->refunds->poll($refund);
            $counts['refunds']++;
        }

        $failed = PaymentWebhook::query()->whereIn('status', [WebhookStatus::Failed, WebhookStatus::Received])
            ->where('attempts', '<', (int) config('payments.webhook_max_attempts'))
            ->where('updated_at', '<=', now()->subMinute())->orderBy('id')->limit(self::BATCH)->pluck('id');

        foreach ($failed as $webhookId) {
            $this->webhooks->process($webhookId);
            $counts['webhooks']++;
        }

        return $counts;
    }

    public function reconcileDaily(PaymentGateway $account, CarbonImmutable $windowEnd): ?ReconciliationRun
    {
        $windowStart = $windowEnd->subHours((int) config('payments.reconciliation_window_hours'));

        try {
            $run = (new ReconciliationRun)->forceFill([
                'kind' => ReconciliationRunKind::Payments,
                'subject_key' => 'gateway:'.$account->id,
                'window_start' => $windowStart,
                'window_end' => $windowEnd,
                'status' => ReconciliationRunStatus::Running,
                'started_at' => now(),
            ]);
            $run->save();
        } catch (UniqueConstraintViolationException) {
            return null;
        }

        $checked = 0;
        $mismatches = 0;

        try {
            $adapter = $this->registry->for($account);

            $payments = Payment::query()->where('payment_gateway_id', $account->id)
                ->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded])
                ->whereBetween('updated_at', [$windowStart, $windowEnd])->whereNotNull('provider_payment_id')
                ->orderBy('id')->get();

            foreach ($payments as $payment) {
                $state = $adapter->fetchState($payment);
                $checked++;
                $type = match (true) {
                    $state->status === ProviderPaymentStatus::NotFound => ReconciliationItemType::MissingProvider,
                    $state->status !== ProviderPaymentStatus::Succeeded => ReconciliationItemType::StatusMismatch,
                    $state->amount === null || ! $state->amount->equals(Money::of($payment->amount, $payment->currency)) => ReconciliationItemType::AmountMismatch,
                    default => null,
                };

                if ($type !== null) {
                    $mismatches++;
                    (new ReconciliationItem)->forceFill([
                        'reconciliation_run_id' => $run->id,
                        'type' => $type,
                        'reference_type' => 'payment',
                        'reference_id' => $payment->id,
                        'provider_reference' => $payment->provider_payment_id,
                        'details' => ['ours' => $payment->status->value.' '.$payment->amount.' '.$payment->currency, 'provider' => $state->status->value.' '.($state->amount === null ? '' : (string) $state->amount)],
                        'status' => ReconciliationItemStatus::Open,
                    ])->save();
                }
            }
        } catch (GatewayRequestFailed|GatewayNotConfigured) {
            $run->forceFill(['status' => ReconciliationRunStatus::Failed, 'checked' => $checked, 'mismatches' => $mismatches, 'finished_at' => now()])->save();

            return $run;
        }

        $run->forceFill(['status' => ReconciliationRunStatus::Completed, 'checked' => $checked, 'mismatches' => $mismatches, 'finished_at' => now()])->save();

        return $run;
    }

    /**
     * An attempt the customer never completed stops being polled once the order's quote has
     * long expired. A `processing` attempt is never expired: the provider still owes an answer.
     */
    private function expireIfAbandoned(Payment $payment): int
    {
        $abandonedBefore = now()->subHours((int) config('payments.expire_unpaid_attempt_after_hours'));

        if (! in_array($payment->status, [PaymentStatus::Created, PaymentStatus::RequiresAction], true)) {
            return 0;
        }

        $reference = $payment->expires_at ?? $payment->created_at;

        if ($reference === null || $reference->greaterThan($abandonedBefore)) {
            return 0;
        }

        return Payment::query()->whereKey($payment->id)->whereIn('status', [PaymentStatus::Created, PaymentStatus::RequiresAction])
            ->update(['status' => PaymentStatus::Expired->value, 'next_check_at' => null]);
    }
}
