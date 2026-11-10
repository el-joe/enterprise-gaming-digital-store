<?php

namespace App\Http\Resources\Admin\Catalog;

use App\Domains\Catalog\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'region_id' => $this->region_id,
            'region' => $this->whenLoaded('region', fn () => $this->region?->code),
            'sku' => $this->sku,
            'title' => $this->title,
            'denomination_value' => $this->denomination_value,
            'denomination_currency' => $this->denomination_currency,
            'is_custom_amount' => $this->is_custom_amount,
            'custom_min' => $this->custom_min,
            'custom_max' => $this->custom_max,
            'manual_cost_base' => $this->manual_cost_base,
            'price_base' => $this->price_base,
            'compare_at_base' => $this->compare_at_base,
            'availability' => $this->availability->value,
            'pricing_dirty_at' => $this->pricing_dirty_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
