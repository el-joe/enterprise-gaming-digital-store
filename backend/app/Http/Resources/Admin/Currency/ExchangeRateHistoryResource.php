<?php

namespace App\Http\Resources\Admin\Currency;

use App\Domains\Currency\Models\ExchangeRateHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExchangeRateHistory
 */
class ExchangeRateHistoryResource extends JsonResource
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
            'quote_currency' => $this->quote_currency,
            'provider_rate' => $this->provider_rate,
            'effective_rate' => $this->effective_rate,
            'source' => $this->source,
            'is_manual' => $this->is_manual,
            'changed_by' => $this->changed_by,
            'captured_at' => $this->captured_at?->toIso8601String(),
        ];
    }
}
