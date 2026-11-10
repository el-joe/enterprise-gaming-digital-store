<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Http\Requests\Web\ListingPageRequest;
use App\Http\Resources\Storefront\ProductCardResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * A page of product cards for a listing page, filtered and priced exactly as the listing JSON
 * (CatalogController::products), with the page's fixed filter (its category or platform).
 */
trait RendersProductListing
{
    /**
     * @param  array{category?: string, platform?: string}  $fixed
     * @return array{products: LengthAwarePaginator, filters: array<string, string|null>, currency: string}
     */
    protected function productListing(ListingPageRequest $request, string $currency, array $fixed = []): array
    {
        $catalog = app(StorefrontCatalogService::class);

        $filters = [
            'category' => $fixed['category'] ?? $request->validated('category'),
            'platform' => $fixed['platform'] ?? $request->validated('platform'),
            'delivery_type' => $request->validated('delivery_type'),
            'min_price' => $request->validated('min_price'),
            'max_price' => $request->validated('max_price'),
            'sort' => $request->validated('sort') ?? 'popular',
        ];

        $page = $catalog->listing(new ProductListingFilter(
            page: (int) ($request->validated('page') ?? 1),
            perPage: (int) config('storefront.per_page'),
            category: $filters['category'],
            platform: $filters['platform'],
            region: $request->validated('region'),
            deliveryType: $filters['delivery_type'],
            minPriceBase: $catalog->filterAmountToBase($filters['min_price'], $currency),
            maxPriceBase: $catalog->filterAmountToBase($filters['max_price'], $currency),
            sort: $filters['sort'],
        ), $currency);

        $products = $page->getCollection()->map(static fn (ProductCard $card): Product => $card->product);
        app(ContentTranslator::class)->preload([...$products, ...$products->map->gamingPlatform, ...$products->map->primaryCategory], ['name', 'short_description']);

        $cards = ProductCardResource::collection($page->getCollection())->resolve($request);
        $page->setCollection(collect($cards));

        return ['products' => $page->withQueryString(), 'filters' => $filters, 'currency' => $currency];
    }
}
