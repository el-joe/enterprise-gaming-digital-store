<?php

namespace App\Http\Resources\Admin\Cms;

use App\Domains\Cms\Models\HomepageSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HomepageSection
 */
class HomepageSectionResource extends JsonResource
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
            'key' => $this->key,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'source' => $this->source->value,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'items' => $this->whenLoaded('homepageSectionItems', fn () => $this->homepageSectionItems->sortBy('sort_order')->map->only(['item_type', 'item_id', 'sort_order'])->values()->all()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
