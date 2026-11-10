<?php

namespace App\Http\Resources\Admin\Fulfillment;

use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Delivery values (player ID and similar) appear only on the single-task view, which staff
 * need in order to perform the top-up.
 *
 * @mixin ManualFulfillmentTask
 */
class ManualTaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $unit = $this->orderItemUnit;

        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'assigned_to' => $this->assigned_to,
            'claimed_at' => $this->claimed_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'completion_note' => $this->completion_note,
            'unit' => $unit === null ? null : [
                'public_id' => $unit->public_id,
                'status' => $unit->status?->value,
                'order' => $unit->relationLoaded('order') && $unit->order !== null ? ['public_id' => $unit->order->public_id, 'order_number' => $unit->order->order_number, 'status' => $unit->order->status->value] : null,
                'item' => $unit->relationLoaded('orderItem') && $unit->orderItem !== null ? ['name' => $unit->orderItem->name_snapshot, 'sku' => $unit->orderItem->sku_snapshot, 'delivery_type' => $unit->orderItem->delivery_type->value] : null,
            ],
            'delivery_values' => $this->when(
                $unit?->orderItem?->relationLoaded('orderItemDeliveryValues') === true,
                fn (): array => $unit->orderItem->orderItemDeliveryValues
                    ->map(static fn (OrderItemDeliveryValue $value): array => ['code' => $value->field_code, 'label' => $value->field_label, 'value' => $value->value])->values()->all(),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
