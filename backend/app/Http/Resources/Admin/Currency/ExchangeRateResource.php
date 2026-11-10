<?php

namespace App\Http\Resources\Admin\Currency;

use App\Domains\Currency\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExchangeRate
 */
class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'base_currency' => $this->base_currency,
            'quote_currency' => $this->quote_currency,
            'provider_rate' => $this->provider_rate,
            'manual_rate' => $this->manual_rate,
            'is_manual_active' => $this->is_manual_active,
            'manual_expires_at' => $this->manual_expires_at?->toIso8601String(),
            'effective_rate' => $this->effective_rate,
            'source' => $this->source,
            'fetched_at' => $this->fetched_at?->toIso8601String(),
            'pending_rate' => $this->pending_rate,
            'pending_since' => $this->pending_since?->toIso8601String(),
        ];
    }
}
