<?php

namespace App\Http\Resources\Admin\Catalog;

use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductImage
 */
class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant_id' => $this->variant_id,
            'media_id' => $this->media_id,
            'alt' => $this->alt,
            'sort_order' => $this->sort_order,
            'path' => $this->whenLoaded('media', fn () => $this->media?->path),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
