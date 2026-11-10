<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Shared\Models\Country;
use App\Domains\Shared\Services\CountryDirectory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Country $resource
 */
class CountryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->resource->code,
            'name' => app(CountryDirectory::class)->localizedName($this->resource),
            'dial_code' => $this->resource->dial_code,
            'default_currency' => $this->resource->default_currency,
        ];
    }
}
