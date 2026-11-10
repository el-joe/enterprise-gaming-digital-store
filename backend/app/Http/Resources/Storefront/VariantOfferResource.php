<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\VariantOffer;
use App\Domains\Catalog\Services\ContentTranslator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read VariantOffer $resource
 */
class VariantOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array. Cost, floor and margin stay on the server.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->resource->variant;
        $quote = $this->resource->quote;

        return [
            'public_id' => $variant->public_id,
            'title' => app(ContentTranslator::class)->text($variant, 'title'),
            'sku' => $variant->sku,
            'region' => $variant->region?->code,
            'denomination_value' => $variant->denomination_value,
            'denomination_currency' => $variant->denomination_currency,
            'is_custom_amount' => $variant->is_custom_amount,
            'availability' => $variant->availability->value,
            'purchasable' => $quote->purchasable,
            'price' => $this->resource->price,
            'price_may_vary' => $quote->rate?->isStale ?? false,
        ];
    }
}
