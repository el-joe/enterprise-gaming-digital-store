<?php

namespace App\Domains\Promotions\Listeners;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Models\CouponRedemption;

/**
 * A reserved coupon use becomes final once the order is paid; a release can no longer return it.
 */
class RedeemCouponWhenPaid
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->to !== OrderStatus::Paid) {
            return;
        }

        CouponRedemption::query()
            ->where('order_id', $event->orderId)
            ->where('status', CouponRedemptionStatus::Reserved->value)
            ->update(['status' => CouponRedemptionStatus::Redeemed->value, 'updated_at' => now()]);
    }
}
