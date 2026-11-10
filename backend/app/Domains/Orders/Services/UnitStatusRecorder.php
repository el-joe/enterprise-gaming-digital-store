<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItemUnit;

/**
 * Writes a unit's status and its timeline entry together. Callers hold the unit's row lock (and
 * the order's, per the lock order) inside their transaction.
 */
class UnitStatusRecorder
{
    /**
     * @param  array<string, mixed>  $metadata  IDs and non-sensitive facts only
     */
    public function move(OrderItemUnit $unit, OrderItemUnitStatus $to, string $event, ?string $message = null, array $metadata = [], ?OrderActor $actor = null): void
    {
        $from = $unit->status;
        $actor ??= OrderActor::system();

        $changes = ['status' => $to];

        if ($to === OrderItemUnitStatus::Delivered && $unit->delivered_at === null) {
            $changes['delivered_at'] = now();
        }

        $unit->forceFill($changes)->save();

        $this->note($unit, $event, $message, ['unit_from' => $from?->value, 'unit_to' => $to->value, ...$metadata], $actor);
    }

    /**
     * A timeline entry about a unit without a status change.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function note(OrderItemUnit $unit, string $event, ?string $message = null, array $metadata = [], ?OrderActor $actor = null): void
    {
        $actor ??= OrderActor::system();

        (new OrderEvent)->forceFill([
            'order_id' => $unit->order_id,
            'order_item_unit_id' => $unit->id,
            'type' => 'unit.'.$event,
            'actor_type' => $actor->type,
            'actor_id' => $actor->id(),
            'message' => $message === null ? null : mb_substr($message, 0, 500),
            'metadata' => $metadata === [] ? null : $metadata,
        ])->save();
    }
}
