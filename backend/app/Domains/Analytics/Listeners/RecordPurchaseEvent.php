<?php

namespace App\Domains\Analytics\Listeners;

use App\Domains\Analytics\Enums\AnalyticsEventDispatchEventName;
use App\Domains\Analytics\Enums\AnalyticsEventDispatchStatus;
use App\Domains\Analytics\Jobs\SendAnalyticsEvent;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;

/**
 * Queues the GA4 `purchase` event once per order, server side, when the order is verified paid
 * (the browser never sends `purchase`). The unique `dedupe_key` (`purchase:{order_number}`)
 * makes a repeated paid transition harmless. M18: the new row is sent by SendAnalyticsEvent after
 * the transaction commits (and retried by `analytics:dispatch-pending`); it stays `pending` while
 * the Measurement Protocol awaits credentials. The payload holds no personal data.
 */
class RecordPurchaseEvent
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->to !== OrderStatus::Paid) {
            return;
        }

        $order = Order::query()->findOrFail($event->orderId);
        $items = OrderItem::query()->where('order_id', $order->id)->orderBy('id')->get(['product_variant_id', 'sku_snapshot', 'quantity']);

        $inserted = AnalyticsEventDispatch::query()->insertOrIgnore([
            'event_name' => AnalyticsEventDispatchEventName::Purchase->value,
            'order_id' => $order->id,
            'dedupe_key' => 'purchase:'.$order->order_number,
            'payload' => json_encode([
                'client_id' => $order->ga_client_id,
                'transaction_id' => $order->order_number,
                'value' => $order->payment_total,
                'currency' => $order->payment_currency,
                'items' => $items->map(static fn (OrderItem $item): array => ['item_id' => $item->sku_snapshot, 'quantity' => $item->quantity])->all(),
            ], JSON_THROW_ON_ERROR),
            'status' => AnalyticsEventDispatchStatus::Pending->value,
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 1) {
            $id = AnalyticsEventDispatch::query()->where('dedupe_key', 'purchase:'.$order->order_number)->value('id');
            SendAnalyticsEvent::dispatch((int) $id)->afterCommit();
        }
    }
}
