<?php

namespace App\Domains\Fulfillment\Contracts;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;

/**
 * Plans one pending unit (architecture 7.5). Runs inside the planner's transaction holding the
 * order and unit locks; must not make external calls. Returns the unit id to submit to a
 * supplier after commit, or null.
 */
interface DeliveryStrategy
{
    public function plan(Order $order, OrderItemUnit $unit): ?int;
}
