<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\DTOs\PriceQuote;
use App\Domains\Pricing\DTOs\PricingContext;

class PriceSimulator
{
    public function __construct(private PricingEngine $engine) {}

    public function simulate(string $variantPublicId, ?string $country, ?string $currency): PriceQuote
    {
        $variant = ProductVariant::query()->where('public_id', $variantPublicId)->firstOrFail();

        return $this->engine->quote($variant, new PricingContext($country, $currency));
    }
}
