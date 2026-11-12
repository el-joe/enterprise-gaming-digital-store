<?php

namespace App\Domains\Seo\Sitemaps;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Catalog\Services\StorefrontTaxonomyService;
use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Services\BlogPostService;
use App\Domains\Seo\Models\SeoMetadata;
use App\Http\Controllers\Web\PageController;
use Illuminate\Database\Eloquent\Model;

/**
 * What the sitemap lists (SEO_STRATEGY.md section 5): indexable storefront pages only. Products
 * as the listing shows them (active with an active priced variant); active categories and
 * platforms with listed products (an empty listing is noindex); published CMS pages and the
 * built-in legal pages; visible blog posts. A `seo_metadata` row with `noindex` removes the page
 * in that language. Search, cart, checkout, account and order pages are never listed.
 */
class SitemapSource
{
    public const array TYPES = ['static', 'categories', 'platforms', 'products', 'pages', 'blog'];

    /** @var array<string, list<string>>|null */
    private ?array $noindex = null;

    public function __construct(
        private StorefrontCatalogService $catalog,
        private StorefrontTaxonomyService $taxonomy,
        private BlogPostService $posts,
    ) {}

    /**
     * @return iterable<SitemapEntry>
     */
    public function entries(string $type): iterable
    {
        return match ($type) {
            'static' => $this->staticPages(),
            'categories' => $this->taxonomyPages($this->taxonomy->categories(), 'storefront.category'),
            'platforms' => $this->taxonomyPages($this->taxonomy->platforms(), 'storefront.brand'),
            'products' => $this->productPages(),
            'pages' => $this->contentPages(),
            'blog' => $this->blogPages(),
        };
    }

    /**
     * @return iterable<SitemapEntry>
     */
    private function staticPages(): iterable
    {
        $latestProduct = Product::query()->max('updated_at');
        $latestPost = $this->posts->visibleQuery()->max('updated_at');

        yield new SitemapEntry('storefront.home', lastModified: $latestProduct === null ? null : now()->parse($latestProduct));
        yield new SitemapEntry('storefront.products.index', lastModified: $latestProduct === null ? null : now()->parse($latestProduct));

        if ($latestPost !== null) {
            yield new SitemapEntry('storefront.blog.index', lastModified: now()->parse($latestPost));
        }
    }

    /**
     * @param  iterable<CatalogNode>  $nodes
     * @return iterable<SitemapEntry>
     */
    private function taxonomyPages(iterable $nodes, string $route): iterable
    {
        foreach ($nodes as $node) {
            if ($node->productCount > 0) {
                yield new SitemapEntry($route, ['slug' => $node->model->slug], $node->model->updated_at, $this->noindexLocales($node->model));
            }
        }
    }

    /**
     * @return iterable<SitemapEntry>
     */
    private function productPages(): iterable
    {
        foreach ($this->catalog->sitemapProducts()->lazyById(1000, 'products.id', 'id') as $product) {
            yield new SitemapEntry('storefront.products.show', ['slug' => $product->slug], $product->updated_at, $this->noindexLocales($product));
        }
    }

    /**
     * @return iterable<SitemapEntry>
     */
    private function contentPages(): iterable
    {
        $published = Page::query()->where('status', PageStatus::Published)->orderBy('id')->get(['id', 'slug', 'updated_at']);

        foreach ($published as $page) {
            yield new SitemapEntry('storefront.pages.show', ['slug' => $page->slug], $page->updated_at, $this->noindexLocales($page));
        }

        foreach (array_diff(PageController::BUILT_IN, $published->pluck('slug')->all()) as $slug) {
            yield new SitemapEntry('storefront.pages.show', ['slug' => $slug]);
        }
    }

    /**
     * @return iterable<SitemapEntry>
     */
    private function blogPages(): iterable
    {
        foreach ($this->posts->visibleQuery()->select(['id', 'slug', 'updated_at'])->lazyById(1000) as $post) {
            /** @var BlogPost $post */
            yield new SitemapEntry('storefront.blog.show', ['slug' => $post->slug], $post->updated_at, $this->noindexLocales($post));
        }
    }

    /**
     * @return list<string>
     */
    private function noindexLocales(Model $record): array
    {
        $this->noindex ??= SeoMetadata::query()
            ->where('robots', 'like', 'noindex%')
            ->get(['seoable_type', 'seoable_id', 'locale'])
            ->groupBy(static fn (SeoMetadata $row): string => $row->seoable_type.':'.$row->seoable_id)
            ->map(static fn ($rows): array => $rows->pluck('locale')->all())
            ->all();

        return $this->noindex[$record->getMorphClass().':'.$record->getKey()] ?? [];
    }
}
