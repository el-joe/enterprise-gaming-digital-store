<?php

namespace App\Http\Resources\Admin\Promotions;

use App\Domains\Promotions\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Coupon
 */
class CouponResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type->value,
            'value' => $this->value,
            'currency' => $this->currency,
            'max_discount_base' => $this->max_discount_base,
            'min_order_base' => $this->min_order_base,
            'usage_limit' => $this->usage_limit,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'used_count' => $this->used_count,
            'applicable_to' => $this->applicable_to,
            'stackable' => $this->stackable,
            'allow_below_margin' => $this->allow_below_margin,
            'first_order_only' => $this->first_order_only,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'created_by_type' => $this->created_by_type->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
