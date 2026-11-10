<?php

namespace App\Http\Resources\Admin\Currency;

use App\Domains\Currency\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'exponent' => $this->exponent,
            'rounding_rule' => $this->rounding_rule,
            'markup_pct' => $this->markup_pct,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
