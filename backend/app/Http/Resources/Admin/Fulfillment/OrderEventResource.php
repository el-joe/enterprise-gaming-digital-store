<?php

namespace App\Http\Resources\Admin\Fulfillment;

use App\Domains\Orders\Models\OrderEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderEvent
 */
class OrderEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'unit_id' => $this->order_item_unit_id,
            'from_status' => $this->from_status?->value,
            'to_status' => $this->to_status?->value,
            'actor_type' => $this->actor_type?->value,
            'actor_id' => $this->actor_id,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
