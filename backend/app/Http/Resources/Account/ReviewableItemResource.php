<?php

namespace App\Http\Resources\Account;

use App\Domains\Orders\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class ReviewableItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_item_id' => $this->id,
            'order' => ['public_id' => $this->order?->public_id, 'order_number' => $this->order?->order_number],
            'product' => ['name' => $this->product?->name ?? $this->name_snapshot, 'slug' => $this->product?->slug],
        ];
    }
}
