<?php

namespace App\Domains\Orders\Listeners;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Notifications\OrderFulfilmentFailed;
use Illuminate\Support\Facades\Notification;

/**
 * Customer notifications for delivery and failure. They carry the order id only and link to
 * the reveal page; no notification ever contains a digital code (ADR-009).
 */
class SendFulfilmentNotifications
{
    private const array FULFILMENT_STATUSES = [OrderStatus::Processing, OrderStatus::SupplierPending, OrderStatus::PendingVerification, OrderStatus::ManualReview];

    public function handle(OrderStatusChanged $event): void
    {
        $notification = match (true) {
            $event->to === OrderStatus::Completed, $event->to === OrderStatus::PartiallyFulfilled => new OrderDelivered($event->orderId),
            $event->to === OrderStatus::RefundPending && in_array($event->from, self::FULFILMENT_STATUSES, true) => new OrderFulfilmentFailed($event->orderId),
            default => null,
        };

        if ($notification === null) {
            return;
        }

        $order = Order::query()->with('user')->find($event->orderId);

        if ($order === null) {
            return;
        }

        if ($order->user !== null) {
            $order->user->notify($notification);
        } else {
            Notification::route('mail', $order->contact_email)->notify($notification);
        }
    }
}
