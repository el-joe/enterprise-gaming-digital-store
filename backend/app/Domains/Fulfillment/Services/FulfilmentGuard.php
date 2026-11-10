<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Rule 9: no unit is planned or submitted unless the order has a verified payment and is in a
 * fulfilment status. Checked again at every step, independently of the state machine.
 */
class FulfilmentGuard
{
    private const array FULFILMENT_STATUSES = [
        OrderStatus::Processing, OrderStatus::SupplierPending, OrderStatus::PendingVerification, OrderStatus::ManualReview, OrderStatus::PartiallyFulfilled,
    ];

    public function allows(Order $order): bool
    {
        $allowed = $order->primary_payment_id !== null && $order->paid_at !== null && in_array($order->status, self::FULFILMENT_STATUSES, true);

        if (! $allowed) {
            Log::alert('fulfilment.refused_unpaid_or_closed', ['order_id' => $order->id, 'status' => $order->status->value]);
        }

        return $allowed;
    }
}
