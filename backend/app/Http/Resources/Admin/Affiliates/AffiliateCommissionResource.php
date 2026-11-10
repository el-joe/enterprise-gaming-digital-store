<?php

namespace App\Http\Resources\Admin\Affiliates;

use App\Domains\Affiliates\Models\AffiliateCommission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AffiliateCommission
 */
class AffiliateCommissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'affiliate_id' => $this->affiliate_id,
            'order' => $this->whenLoaded('order', fn (): array => ['public_id' => $this->order->public_id, 'order_number' => $this->order->order_number, 'status' => $this->order->status->value]),
            'amount_base' => $this->amount_base,
            'status' => $this->status->value,
            'payout_id' => $this->payout_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
