<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Region;
use App\Domains\Seo\Models\SeoMetadata;

/**
 * Storefront product page data (M-A4). The card carries the product and its "from" price in the
 * display currency, computed as the listing computes it.
 */
final readonly class ProductDetail
{
    /**
     * @param  list<DeliveryFieldSpec>  $deliveryFields
     * @param  list<Region>  $variantRegions  regions of the active variants
     */
    public function __construct(
        public ProductCard $card,
        public array $deliveryFields,
        public int $variantCount,
        public ?ProductVariantAvailability $availability,
        public bool $hasCustomAmount,
        public array $variantRegions,
        public ?SeoMetadata $seo,
    ) {}
}
