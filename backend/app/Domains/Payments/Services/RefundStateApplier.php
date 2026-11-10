<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderFinancialsWriter;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\PaymentTransactionType;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies a provider refund result under the lock order `orders`, `payments`, refund,
 * `order_item_units`. Final states are never left: a repeated or late result is a no-op.
 *
 * - Succeeded: units refunded, a ledger row, the payment partially or fully refunded, financials
 *   corrected, and the order (when it is `refund_pending` and nothing else is open) to
 *   `refunded` when the whole payment is back, otherwise `partially_refunded`.
 * - Failed: the units return to their previous status and are released for a new refund; the
 *   order goes to `manual_review` for staff, with an alert.
 */
class RefundStateApplier
{
    private const array OPEN = [RefundStatus::Requested, RefundStatus::Pending, RefundStatus::Unknown];

    public function __construct(
        private OrderStateMachine $machine,
        private UnitStatusRecorder $units,
        private PaymentStateApplier $payments,
        private OrderFinancialsWriter $financials,
    ) {}

    public function apply(int $refundId, RefundResult $result): Refund
    {
        $ids = Refund::query()->whereKey($refundId)->firstOrFail(['order_id', 'payment_id']);

        return DB::transaction(function () use ($ids, $refundId, $result): Refund {
            $order = Order::query()->lockForUpdate()->findOrFail($ids->order_id);
            $payment = Payment::query()->lockForUpdate()->findOrFail($ids->payment_id);
            $refund = Refund::query()->lockForUpdate()->findOrFail($refundId);

            if (in_array($refund->status, [RefundStatus::Succeeded, RefundStatus::Failed], true)) {
                return $refund;
            }

            match ($result->status) {
                RefundStatus::Succeeded => $this->succeeded($order, $payment, $refund, $result),
                RefundStatus::Failed => $this->failed($order, $refund, $result),
                RefundStatus::Pending, RefundStatus::Unknown => $refund->forceFill([
                    'status' => $result->status,
                    'provider_refund_id' => $refund->provider_refund_id ?? $result->providerRefundId,
                ])->save(),
                RefundStatus::Requested => null,
            };

            return $refund;
        }, 3);
    }

    private function succeeded(Order $order, Payment $payment, Refund $refund, RefundResult $result): void
    {
        $refund->forceFill([
            'status' => RefundStatus::Succeeded,
            'provider_refund_id' => $refund->provider_refund_id ?? $result->providerRefundId,
            'completed_at' => now(),
            'failure_reason' => null,
        ])->save();

        $this->payments->ledger($payment, PaymentTransactionType::Refund, $refund->provider_refund_id ?? 'refund:'.$refund->public_id, Money::of($refund->amount, $refund->currency));

        $units = OrderItemUnit::query()->whereHas('refunds', static fn ($query) => $query->whereKey($refund->id))->lockForUpdate()->get();

        foreach ($units as $unit) {
            $this->units->move($unit, OrderItemUnitStatus::Refunded, 'refunded', 'Refund confirmed by the provider.', ['refund_id' => $refund->id]);
            $unit->forceFill(['refunded_at' => now()])->save();
        }

        $refundedTotal = Money::of((string) Refund::query()->where('payment_id', $payment->id)->where('status', RefundStatus::Succeeded)->sum('amount'), $payment->currency);
        $fullyRefunded = $refundedTotal->compareTo(Money::of($payment->amount, $payment->currency)) >= 0;

        if (in_array($payment->status, [PaymentStatus::Succeeded, PaymentStatus::PartiallyRefunded], true)) {
            $payment->forceFill(['status' => $fullyRefunded ? PaymentStatus::Refunded : PaymentStatus::PartiallyRefunded])->save();
        }

        if ($payment->id !== $order->primary_payment_id) {
            return;
        }

        $this->financials->recordRefunds($order->id);

        if ($order->status === OrderStatus::RefundPending && ! $this->hasOpenRefunds($order)) {
            $this->machine->transition($order, $fullyRefunded ? OrderStatus::Refunded : OrderStatus::PartiallyRefunded, OrderActor::system(), 'Refund confirmed by the provider.', ['refund_id' => $refund->id]);
        }
    }

    private function failed(Order $order, Refund $refund, RefundResult $result): void
    {
        $refund->forceFill(['status' => RefundStatus::Failed, 'failure_reason' => mb_substr((string) ($result->failureReason ?? 'failed'), 0, 255)])->save();

        $units = OrderItemUnit::query()->whereHas('refunds', static fn ($query) => $query->whereKey($refund->id))->lockForUpdate()->get();

        foreach ($units as $unit) {
            $previous = $unit->delivered_at !== null ? OrderItemUnitStatus::Delivered : OrderItemUnitStatus::Failed;
            $this->units->move($unit, $previous, 'refund_failed', 'Refund failed; the unit can be refunded again.', ['refund_id' => $refund->id]);
        }

        $refund->units()->detach();

        Log::alert('payments.refund_failed', ['refund_id' => $refund->id, 'order_id' => $order->id, 'reason' => $refund->failure_reason]);

        if ($order->status === OrderStatus::RefundPending && ! $this->hasOpenRefunds($order)) {
            $this->machine->transition($order, OrderStatus::ManualReview, OrderActor::system(), 'Refund failed at the provider.', ['refund_id' => $refund->id]);
        }
    }

    private function hasOpenRefunds(Order $order): bool
    {
        return Refund::query()->where('order_id', $order->id)->where('payment_id', $order->primary_payment_id)->whereIn('status', self::OPEN)->exists();
    }
}
