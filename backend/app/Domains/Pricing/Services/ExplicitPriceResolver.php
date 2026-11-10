<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Models\VariantPrice;

/**
 * Step 2 of the precedence: (variant, country, currency) beats (variant, currency), which beats
 * (variant, country) priced in another currency.
 */
class ExplicitPriceResolver
{
    public const string ANY_COUNTRY = '*';

    public function resolve(ProductVariant $variant, ?string $country, string $currency): ?VariantPrice
    {
        $active = $variant->variantPrices
            ->filter(static fn (VariantPrice $price): bool => ($price->starts_at === null || $price->starts_at->isPast())
                && ($price->ends_at === null || $price->ends_at->isFuture()))
            ->sortByDesc(static fn (VariantPrice $price): int => $price->starts_at?->getTimestamp() ?? 0);

        return $active->first(static fn (VariantPrice $price): bool => $country !== null && $price->country_code === $country && $price->currency === $currency)
            ?? $active->first(static fn (VariantPrice $price): bool => $price->country_code === self::ANY_COUNTRY && $price->currency === $currency)
            ?? $active->first(static fn (VariantPrice $price): bool => $country !== null && $price->country_code === $country);
    }
}
