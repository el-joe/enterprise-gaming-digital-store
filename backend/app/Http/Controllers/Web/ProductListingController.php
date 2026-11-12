<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontNavigationService;
use App\Domains\Catalog\Services\StorefrontTaxonomyService;
use App\Domains\Seo\DTOs\ResolvedSeo;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Domains\Seo\StructuredData\StructuredDataBuilder;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RendersProductListing;
use App\Http\Requests\Web\ListingPageRequest;
use App\Http\Resources\Storefront\CatalogNodeResource;
use App\Http\Support\LocalizedRoutes;
use App\Http\Support\StorefrontRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;

/**
 * Storefront v2 listings (docs/STOREFRONT_IA.md): every product (`/products`), one category
 * (`/c/{slug}`, the Category facet hidden) and one brand (`/b/{slug}`, the Category and Brand
 * facets hidden), with the side filters, sort and crawlable pagination.
 */
class ProductListingController extends Controller
{
    use RendersProductListing;

    public function __construct(private StorefrontTaxonomyService $taxonomy, private ContentTranslator $translator, private StorefrontRequest $storefront) {}

    public function index(ListingPageRequest $request, StorefrontNavigationService $navigation): View
    {
        $url = LocalizedRoutes::url('storefront.products.index');
        $listing = $this->productListing($request, $this->storefront->currency($request), $url);
        $menu = $navigation->build();

        $seo = new ResolvedSeo(
            title: (string) __('storefront.catalog.all_products'),
            description: (string) __('storefront.catalog.meta', ['site' => config('brand.name')]),
        );
        $this->collectionSchema($seo, $listing['products'], $url);

        return view('storefront.listing.index', [
            'seo' => $seo,
            'categoryCount' => count($menu['categories']),
            ...$listing,
        ]);
    }

    public function category(ListingPageRequest $request, string $slug, StorefrontNavigationService $navigation): View
    {
        $categories = $this->taxonomy->categories();
        $this->translator->preload($categories->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        $node = $categories->first(static fn (CatalogNode $node): bool => $node->model->slug === $slug)
            ?? throw (new ModelNotFoundException)->setModel(Category::class);
        $category = (new CatalogNodeResource($node))->resolve($request);
        $url = LocalizedRoutes::url('storefront.category', ['slug' => $slug]);
        $listing = $this->productListing($request, $this->storefront->currency($request), $url, ['category' => $slug], ['category']);

        $seo = $this->listingSeo(
            SeoTemplatePageType::Category,
            $node->model,
            (string) $category['name'],
            (string) $category['name'],
            $category['description'] ?: __('storefront.meta.category', ['name' => $category['name']]),
            $listing['products'],
            $url,
        );

        $menu = collect($navigation->build()['categories'])->firstWhere('slug', $slug);

        return view('storefront.listing.category', [
            'category' => $category,
            'topBrands' => array_slice($menu['brands'] ?? [], 0, 6),
            'brandCount' => $menu['brand_count'] ?? 0,
            'seo' => $seo,
            ...$listing,
        ]);
    }

    public function brand(ListingPageRequest $request, string $slug): View
    {
        $nodes = $this->taxonomy->platforms();
        $node = $nodes->first(static fn (CatalogNode $node): bool => $node->model->slug === $slug)
            ?? throw (new ModelNotFoundException)->setModel(GamingPlatform::class);
        $this->translator->preload([$node->model], ['name', 'description']);

        $brand = (new CatalogNodeResource($node))->resolve($request);
        $url = LocalizedRoutes::url('storefront.brand', ['slug' => $slug]);
        $listing = $this->productListing($request, $this->storefront->currency($request), $url, ['platform' => $slug], ['category', 'platform']);

        $seo = $this->listingSeo(
            SeoTemplatePageType::Platform,
            $node->model,
            (string) $brand['name'],
            __('storefront.platforms.page_title', ['name' => $brand['name']]),
            $brand['description'] ?: __('storefront.meta.platform', ['name' => $brand['name']]),
            $listing['products'],
            $url,
        );

        return view('storefront.listing.brand', [
            'brand' => $brand,
            'index' => $nodes->search($node),
            'seo' => $seo,
            ...$listing,
        ]);
    }

    /**
     * @param  LengthAwarePaginator<int, array<string, mixed>>  $products
     */
    private function collectionSchema(ResolvedSeo $seo, $products, string $url): void
    {
        $items = collect($products->items())->map(static fn (array $card): array => [
            'name' => (string) $card['name'],
            'url' => LocalizedRoutes::url('storefront.products.show', ['slug' => $card['slug']]),
        ])->values()->all();

        app(PageSchema::class)->add(app(StructuredDataBuilder::class)->collectionPage($seo->title, (string) $seo->description, $url, app()->getLocale(), $items));
    }
}
