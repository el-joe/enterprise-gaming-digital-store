<?php

namespace App\Domains\Fulfillment\Strategies;

use App\Domains\Fulfillment\Contracts\DeliveryStrategy;
use App\Domains\Fulfillment\Enums\ManualFulfillmentTaskStatus;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\UnitStatusRecorder;

/**
 * Staff fulfilment: one task per unit (unique on order_item_unit_id), unit `manual_task`.
 * Used for `manual` products and, at launch (BD2), for direct top-ups and account recharges.
 */
class ManualTaskStrategy implements DeliveryStrategy
{
    public function __construct(private UnitStatusRecorder $units) {}

    public function plan(Order $order, OrderItemUnit $unit): ?int
    {
        $task = ManualFulfillmentTask::query()->where('order_item_unit_id', $unit->id)->first();

        if ($task === null) {
            $task = (new ManualFulfillmentTask)->forceFill(['order_item_unit_id' => $unit->id, 'status' => ManualFulfillmentTaskStatus::Open]);
            $task->save();
        }

        $this->units->move($unit, OrderItemUnitStatus::ManualTask, 'manual_task_created', 'Waiting for staff fulfilment.', ['manual_task_id' => $task->id]);

        return null;
    }
}
