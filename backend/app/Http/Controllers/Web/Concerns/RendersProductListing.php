<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Domains\Catalog\DTOs\ListingFacets;
use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Catalog\Services\StorefrontFacetService;
use App\Domains\Seo\DTOs\ResolvedSeo;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Domains\Seo\StructuredData\StructuredDataBuilder;
use App\Http\Requests\Web\ListingPageRequest;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Support\ListingState;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Storefront v2 listing (`/products`, `/c/{slug}`, `/b/{slug}`): a page of product cards
 * filtered and priced exactly as the listing JSON, the facets with their counts, the URL state
 * (chips, clear, pagination) and the SEO rules of SEO_STRATEGY.md section 2.
 */
trait RendersProductListing
{
    /**
     * @param  array{category?: string, platform?: string}  $fixed  the page's own category or brand
     * @param  list<string>  $hidden  facets the sidebar leaves out
     * @return array{products: LengthAwarePaginator, facets: ListingFacets, state: ListingState, currency: string, robots: string, canonical: string, page: int}
     */
    protected function productListing(ListingPageRequest $request, string $currency, string $baseUrl, array $fixed = [], array $hidden = []): array
    {
        $catalog = app(StorefrontCatalogService::class);
        $filter = $request->listingFilter($currency, (int) config('storefront.per_page'), $fixed, ListingState::DEFAULT_SORT);
        $state = ListingState::fromRequest($request, $baseUrl, array_keys($fixed));

        $page = $catalog->listing($filter, $currency);

        $products = $page->getCollection()->map(static fn (ProductCard $card): Product => $card->product);
        app(ContentTranslator::class)->preload([...$products, ...$products->map->gamingPlatform, ...$products->map->primaryCategory], ['name', 'short_description']);

        $page->setCollection(collect(ProductCardResource::collection($page->getCollection())->resolve($request)));

        $facets = app(StorefrontFacetService::class)->facets($filter, $currency, $hidden, $page->total(), ['min' => $state->minPrice, 'max' => $state->maxPrice]);
        $filtered = $state->filterCount();
        $number = $page->currentPage();

        return [
            'products' => $page,
            'facets' => $facets,
            'state' => $state,
            'currency' => $currency,
            'page' => $number,
            'robots' => $filtered > 1 || $page->isEmpty() ? 'noindex,follow' : 'index,follow',
            'canonical' => $filtered === 0 && $number > 1 ? $baseUrl.'?page='.$number : $baseUrl,
        ];
    }

    /**
     * SEO values of a listing (M18: `seo_metadata`, then templates, then the default) and its
     * CollectionPage JSON-LD listing the products on this page.
     *
     * @param  LengthAwarePaginator<int, array<string, mixed>>  $products
     */
    protected function listingSeo(SeoTemplatePageType $type, ?Model $model, string $name, string $defaultTitle, string $defaultDescription, LengthAwarePaginator $products, string $url): ResolvedSeo
    {
        $seo = app(SeoResolver::class)->resolve($type, $model, [
            'name' => $name,
            $type === SeoTemplatePageType::Platform ? 'platform' : 'category' => $name,
        ], $defaultTitle, $defaultDescription);

        $items = collect($products->items())->map(static fn (array $card): array => [
            'name' => (string) $card['name'],
            'url' => LocalizedRoutes::url('storefront.products.show', ['slug' => $card['slug']]),
        ])->values()->all();

        app(PageSchema::class)->add(app(StructuredDataBuilder::class)->collectionPage($seo->title, $seo->description, $url, app()->getLocale(), $items));

        return $seo;
    }
}
