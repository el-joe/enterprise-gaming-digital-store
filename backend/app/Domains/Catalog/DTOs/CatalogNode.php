<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;

/**
 * A storefront platform or category with the number of products the listing shows for it.
 */
final readonly class CatalogNode
{
    public function __construct(
        public GamingPlatform|Category $model,
        public int $productCount,
    ) {}
}
