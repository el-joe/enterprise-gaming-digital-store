<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Seo\DTOs\ResolvedSeo;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Domains\Seo\StructuredData\StructuredDataBuilder;
use App\Http\Requests\Web\ListingPageRequest;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * SEO values of a category or platform listing (M18: `seo_metadata`, then templates, then
     * the default) and its CollectionPage JSON-LD listing the products on this page.
     *
     * @param  LengthAwarePaginator<int, array<string, mixed>>  $products
     */
    protected function listingSeo(SeoTemplatePageType $type, Model $model, string $name, string $defaultTitle, string $defaultDescription, LengthAwarePaginator $products, string $url): ResolvedSeo
    {
        $seo = app(SeoResolver::class)->resolve($type, $model, [
            'name' => $name,
            $type === SeoTemplatePageType::Category ? 'category' : 'platform' => $name,
        ], $defaultTitle, $defaultDescription);

        $items = collect($products->items())->map(static fn (array $card): array => [
            'name' => (string) $card['name'],
            'url' => LocalizedRoutes::url('storefront.products.show', ['slug' => $card['slug']]),
        ])->values()->all();

        app(PageSchema::class)->add(app(StructuredDataBuilder::class)->collectionPage($seo->title, $seo->description, $url, app()->getLocale(), $items));

        return $seo;
    }
}
