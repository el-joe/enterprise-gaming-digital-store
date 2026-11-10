<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Fulfillment\Contracts\DeliveryStrategy;
use App\Domains\Fulfillment\Jobs\SubmitUnitToSupplier;
use App\Domains\Fulfillment\Strategies\CodeDeliveryStrategy;
use App\Domains\Fulfillment\Strategies\DirectTopupStrategy;
use App\Domains\Fulfillment\Strategies\ManualTaskStrategy;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderProgress;
use App\Domains\Orders\Services\OrderRiskGate;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;

/**
 * FulfillOrder (architecture 13 step 6). A `paid` order goes through the risk gate first, which
 * moves it to `processing` or `manual_review`; a `processing` order has its pending units
 * planned by delivery strategy. Planning locks the order, then each unit, and submits nothing
 * until the transaction commits.
 */
class FulfilmentPlanner
{
    public function __construct(
        private OrderRiskGate $risk,
        private FulfilmentGuard $guard,
        private OrderProgress $progress,
        private Container $container,
    ) {}

    public function start(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if ($order === null || ! OrderItemUnit::query()->where('order_id', $orderId)->exists()) {
            return;
        }

        if ($order->status === OrderStatus::Paid) {
            if ($order->primary_payment_id !== null && $order->paid_at !== null) {
                $this->risk->evaluate($order);
            }

            return;
        }

        if ($order->status === OrderStatus::Processing) {
            $this->planPendingUnits($orderId);
        }
    }

    private function planPendingUnits(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatus::Processing || ! $this->guard->allows($order)) {
                return;
            }

            $units = OrderItemUnit::query()->with('orderItem')->where('order_id', $orderId)
                ->where('status', OrderItemUnitStatus::Pending)->orderBy('id')->lockForUpdate()->get();

            foreach ($units as $unit) {
                $submit = $this->strategyFor($unit->orderItem->delivery_type)->plan($order, $unit);

                if ($submit !== null) {
                    SubmitUnitToSupplier::dispatch($submit)->afterCommit();
                }
            }

            $this->progress->advance($order, OrderActor::system());
        });
    }

    private function strategyFor(DeliveryType $type): DeliveryStrategy
    {
        return $this->container->make(match ($type) {
            DeliveryType::InstantCode, DeliveryType::ApiDelivery, DeliveryType::SubscriptionActivation => CodeDeliveryStrategy::class,
            DeliveryType::DirectTopup, DeliveryType::AccountRecharge => DirectTopupStrategy::class,
            DeliveryType::Manual => ManualTaskStrategy::class,
        });
    }
}
