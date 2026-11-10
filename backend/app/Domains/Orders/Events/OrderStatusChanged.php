<?php

namespace App\Domains\Orders\Events;

use App\Domains\Orders\Enums\OrderStatus;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised after the transaction that changed an order's status commits. `from` is null when the
 * order was just placed. Carries IDs only.
 */
class OrderStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $orderId,
        public readonly ?OrderStatus $from,
        public readonly OrderStatus $to,
    ) {}
}
