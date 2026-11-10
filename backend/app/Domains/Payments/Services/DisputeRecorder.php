<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\DTOs\DisputeData;
use App\Domains\Payments\Enums\PaymentDisputeStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentDispute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Disputes never change order status (architecture 6.2): they are recorded, the order's
 * `has_open_dispute` flag follows whether any dispute on its payments is open, and finance is
 * alerted.
 */
class DisputeRecorder
{
    public function record(Payment $payment, DisputeData $dispute): PaymentDispute
    {
        return DB::transaction(function () use ($payment, $dispute): PaymentDispute {
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);

            $row = PaymentDispute::query()->firstOrNew(['payment_id' => $payment->id, 'provider_dispute_id' => $dispute->providerDisputeId]);
            $row->forceFill([
                'status' => $dispute->status,
                'reason' => $dispute->reason === null ? null : mb_substr($dispute->reason, 0, 128),
                'amount' => $dispute->amount->amount,
                'currency' => $dispute->amount->currency,
                'evidence_due_at' => $dispute->evidenceDueAt,
                'closed_at' => $dispute->status === PaymentDisputeStatus::Open ? null : ($row->closed_at ?? now()),
            ])->save();

            $open = PaymentDispute::query()->whereIn('payment_id', Payment::query()->where('order_id', $order->id)->select('id'))
                ->where('status', PaymentDisputeStatus::Open)->exists();

            $order->forceFill(['has_open_dispute' => $open])->save();

            Log::alert('payments.dispute', ['order_id' => $order->id, 'payment_id' => $payment->id, 'dispute_id' => $row->id, 'status' => $dispute->status->value]);

            return $row;
        });
    }
}
