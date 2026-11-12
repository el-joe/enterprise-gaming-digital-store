<?php

namespace App\Domains\Catalog\DTOs;

/**
 * A search suggestion. Storefront v2: `image` is the product's image (ProductImages: product image,
 * else brand logo), null for platforms, categories and products without either.
 */
final readonly class SearchSuggestion
{
    public function __construct(
        public string $type,
        public string $title,
        public string $slug,
        public ?array $image = null,
    ) {}
}
