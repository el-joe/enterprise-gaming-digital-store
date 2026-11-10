<?php

namespace App\Http\Resources\Admin\Pricing;

use App\Domains\Pricing\Models\PricingRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PricingRule
 */
class PricingRuleResource extends JsonResource
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
            'name' => $this->name,
            'scope' => $this->scope->value,
            'scope_id' => $this->scope_id,
            'kind' => $this->kind->value,
            'country_code' => $this->country_code,
            'currency' => $this->currency,
            'fixed_markup' => $this->fixed_markup,
            'percent_markup' => $this->percent_markup,
            'min_margin_pct' => $this->min_margin_pct,
            'min_margin_fixed' => $this->min_margin_fixed,
            'below_margin_policy' => $this->below_margin_policy?->value,
            'conditions' => $this->conditions,
            'priority' => $this->priority,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
