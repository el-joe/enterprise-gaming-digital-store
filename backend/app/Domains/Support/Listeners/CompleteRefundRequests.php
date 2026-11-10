<?php

namespace App\Domains\Support\Listeners;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Support\Services\RefundRequestService;

class CompleteRefundRequests
{
    public function __construct(private RefundRequestService $requests) {}

    public function handle(OrderStatusChanged $event): void
    {
        if (in_array($event->to, [OrderStatus::Refunded, OrderStatus::PartiallyRefunded], true)) {
            $this->requests->completeSettled($event->orderId);
        }
    }
}
