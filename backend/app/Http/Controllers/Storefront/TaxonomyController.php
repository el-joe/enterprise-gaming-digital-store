<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontNavigationService;
use App\Domains\Catalog\Services\StorefrontTaxonomyService;
use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Cms\Services\LinkTargetResolver;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\CatalogNodeResource;
use Illuminate\Http\JsonResponse;

class TaxonomyController extends Controller
{
    public function __construct(private StorefrontTaxonomyService $taxonomy, private ContentTranslator $translator) {}

    public function platforms(): JsonResponse
    {
        $platforms = $this->taxonomy->platforms();
        $this->translator->preload($platforms->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        return $this->success(['platforms' => CatalogNodeResource::collection($platforms)->resolve()]);
    }

    /**
     * Storefront v2 navigation (header and mega menu): top-level categories with counts, their
     * top brands and popular delivery types, the featured header categories and the top brands.
     */
    public function navigation(StorefrontNavigationService $navigation, LinkTargetResolver $links): JsonResponse
    {
        $menu = $navigation->build();
        $brand = static fn (array $brand): array => [...$brand, 'link' => $links->forEntity(LinkTargetType::Platform, $brand['slug'])->toArray()];

        return $this->success([
            'total' => $menu['total'],
            'all_products' => $links->forListing()->toArray(),
            'categories' => array_map(static fn (array $category): array => [
                'slug' => $category['slug'],
                'name' => $category['name'],
                'product_count' => $category['count'],
                'image' => $category['image'],
                'brand_count' => $category['brand_count'],
                'brands' => array_map($brand, $category['brands']),
                'popular_filters' => array_map(static fn (string $type): array => ['key' => 'delivery_type', 'value' => $type, 'label' => (string) __('storefront.delivery.'.$type)], $category['delivery_types']),
                'link' => $links->forEntity(LinkTargetType::Category, $category['slug'])->toArray(),
            ], $menu['categories']),
            'featured' => array_map(static fn (array $category): array => [...$category, 'link' => $links->forEntity(LinkTargetType::Category, $category['slug'])->toArray()], $menu['featured']),
            'brands' => array_map($brand, $menu['brands']),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = $this->taxonomy->categories();
        $this->translator->preload($categories->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        return $this->success(['categories' => CatalogNodeResource::collection($categories)->resolve()]);
    }
}
