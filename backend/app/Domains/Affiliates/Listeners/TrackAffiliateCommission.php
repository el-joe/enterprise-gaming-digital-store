<?php

namespace App\Domains\Affiliates\Listeners;

use App\Domains\Affiliates\Services\AffiliateCommissionService;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;

/**
 * Accrues commission when an attributed order completes and recomputes it when the order is
 * refunded in full or in part.
 */
class TrackAffiliateCommission
{
    public function __construct(private AffiliateCommissionService $commissions) {}

    public function handle(OrderStatusChanged $event): void
    {
        match ($event->to) {
            OrderStatus::Completed => $this->commissions->accrue($event->orderId),
            OrderStatus::Refunded, OrderStatus::PartiallyRefunded => $this->commissions->adjustForRefund($event->orderId),
            default => null,
        };
    }
}
