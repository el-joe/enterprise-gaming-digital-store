<?php

namespace App\Http\Resources\Admin\Affiliates;

use App\Domains\Affiliates\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Payout details are write-only: only whether they are set is returned.
 *
 * @mixin Affiliate
 */
class AffiliateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn (): array => ['id' => $this->user->id, 'name' => $this->user->name, 'email' => $this->user->email]),
            'code' => $this->code,
            'status' => $this->status->value,
            'commission_pct' => $this->commission_pct,
            'payout_details_configured' => $this->getRawOriginal('payout_details') !== null,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
