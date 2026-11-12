<?php

namespace App\Domains\Cms\DTOs;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Faq;
use App\Domains\Reviews\Models\Review;

/**
 * One resolved entry of a homepage section. `type` is `product`, `platform`, `category`,
 * `banner`, `faq` or `review`. Storefront v2 tiles carry the category's or brand's listed
 * product count when known.
 */
final readonly class HomeItem
{
    public function __construct(
        public string $type,
        public ProductCard|GamingPlatform|Category|Banner|Faq|Review $payload,
        public ?int $productCount = null,
    ) {}
}
