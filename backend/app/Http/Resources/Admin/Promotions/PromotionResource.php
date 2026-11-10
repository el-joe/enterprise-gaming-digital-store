<?php

namespace App\Http\Resources\Admin\Promotions;

use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Promotion
 */
class PromotionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'campaign' => $this->campaign,
            'type' => $this->type->value,
            'value' => $this->value,
            'priority' => $this->priority,
            'stackable' => $this->stackable,
            'badge_text' => $this->badge_text,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'targets' => $this->whenLoaded('promotionTargets', fn (): array => $this->promotionTargets
                ->map(static fn (PromotionTarget $target): array => ['type' => $target->target_type->value, 'id' => $target->target_id])->values()->all()),
        ];
    }
}
