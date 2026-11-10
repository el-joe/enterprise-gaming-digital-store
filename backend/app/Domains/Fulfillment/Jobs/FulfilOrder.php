<?php

namespace App\Domains\Fulfillment\Jobs;

use App\Domains\Fulfillment\Services\FulfilmentPlanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Risk gate for a paid order, then unit planning once it is processing. Carries the order id
 * only; repeating it is harmless because only `pending` units are planned.
 */
class FulfilOrder implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $orderId)
    {
        $this->onQueue('fulfillment');
    }

    public function handle(FulfilmentPlanner $planner): void
    {
        $planner->start($this->orderId);
    }
}
