<?php

namespace App\Domains\Fulfillment\Strategies;

use App\Domains\Fulfillment\Contracts\DeliveryStrategy;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;

/**
 * Player-ID top-ups and account recharges. No integrated supplier supports them (Reloadly has no
 * such API, INTEGRATIONS.md finding 1), so at launch they are staff tasks (owner decision BD2).
 * When a supplier that declares these delivery types is added, this strategy is where it plugs in.
 */
class DirectTopupStrategy implements DeliveryStrategy
{
    public function __construct(private ManualTaskStrategy $manual) {}

    public function plan(Order $order, OrderItemUnit $unit): ?int
    {
        return $this->manual->plan($order, $unit);
    }
}
