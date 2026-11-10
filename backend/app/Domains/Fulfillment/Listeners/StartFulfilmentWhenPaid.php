<?php

namespace App\Domains\Fulfillment\Listeners;

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;

/**
 * Fulfilment starts only from a verified paid state: entering `paid` runs the risk gate, and
 * entering `processing` (after the gate, an approved review, or a verified fallback) plans
 * pending units. Both transitions require a verified payment in the state machine (rule 9).
 */
class StartFulfilmentWhenPaid
{
    public function handle(OrderStatusChanged $event): void
    {
        if (in_array($event->to, [OrderStatus::Paid, OrderStatus::Processing], true)) {
            FulfilOrder::dispatch($event->orderId);
        }
    }
}
