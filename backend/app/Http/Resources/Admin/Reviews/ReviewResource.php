<?php

namespace App\Http\Resources\Admin\Reviews;

use App\Domains\Reviews\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', fn (): array => ['id' => $this->product->id, 'name' => $this->product->name, 'slug' => $this->product->slug]),
            'user' => $this->whenLoaded('user', fn (): ?array => $this->user === null ? null : ['id' => $this->user->id, 'name' => $this->user->name, 'email' => $this->user->email]),
            'order_item_id' => $this->order_item_id,
            'rating' => $this->rating,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status->value,
            'is_featured' => $this->is_featured,
            'moderated_by' => $this->moderated_by,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
