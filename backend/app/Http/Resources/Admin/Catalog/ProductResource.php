<?php

namespace App\Http\Resources\Admin\Catalog;

use App\Domains\Catalog\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'terms' => $this->terms,
            'status' => $this->status->value,
            'delivery_type' => $this->delivery_type->value,
            'delivery_time_minutes' => $this->delivery_time_minutes,
            'max_quantity_per_order' => $this->max_quantity_per_order,
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'gaming_platform_id' => $this->gaming_platform_id,
            'primary_category_id' => $this->primary_category_id,
            'platform' => $this->whenLoaded('gamingPlatform', fn (): ?string => $this->gamingPlatform?->name),
            'category' => $this->whenLoaded('primaryCategory', fn (): ?string => $this->primaryCategory?->name),
            'variants_count' => $this->whenCounted('productVariants'),
            'category_ids' => $this->whenLoaded('categories', fn (): array => $this->categories->pluck('id')->all()),
            'region_codes' => $this->whenLoaded('regions', fn (): array => $this->regions->pluck('code')->all()),
            'delivery_fields' => $this->whenLoaded('productDeliveryFields', fn (): array => $this->productDeliveryFields->map->only(['delivery_field_definition_id', 'is_required', 'sort_order'])->values()->all()),
            'country_restrictions' => $this->whenLoaded('productCountryRestrictions', fn (): array => $this->productCountryRestrictions->map(static fn ($restriction): array => ['country_code' => $restriction->country_code, 'mode' => $restriction->mode->value])->values()->all()),
            'attribute_values' => $this->whenLoaded('productAttributeValues', fn (): array => $this->productAttributeValues->map->only(['attribute_definition_id', 'value'])->values()->all()),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
