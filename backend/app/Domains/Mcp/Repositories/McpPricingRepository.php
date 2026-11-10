<?php

namespace App\Domains\Mcp\Repositories;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Models\VariantPrice;

class McpPricingRepository
{
    /**
     * The explicit price row without a start date for this variant, country and currency.
     */
    public function undatedExplicitPrice(ProductVariant $variant, string $country, string $currency): ?VariantPrice
    {
        return VariantPrice::query()
            ->where('product_variant_id', $variant->id)
            ->where('country_code', $country)
            ->where('currency', $currency)
            ->whereNull('starts_at')
            ->first();
    }
}
