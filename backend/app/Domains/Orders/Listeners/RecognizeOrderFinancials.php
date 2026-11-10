<?php

namespace App\Domains\Orders\Listeners;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Services\OrderFinancialsWriter;

/**
 * Writes `order_item_financials` when an order settles (delivered in full or in part, or
 * refunded without delivering), and corrects them on later refunds.
 */
class RecognizeOrderFinancials
{
    private const array SETTLED = [OrderStatus::Completed, OrderStatus::PartiallyFulfilled, OrderStatus::Refunded, OrderStatus::PartiallyRefunded];

    public function __construct(private OrderFinancialsWriter $writer) {}

    public function handle(OrderStatusChanged $event): void
    {
        if (in_array($event->to, self::SETTLED, true)) {
            $this->writer->recognize($event->orderId);
        }
    }
}
