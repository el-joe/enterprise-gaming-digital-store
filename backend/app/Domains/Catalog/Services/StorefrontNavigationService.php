<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Support\Facades\Cache;

/**
 * Storefront v2 navigation (docs/STOREFRONT_IA.md, Figma "Mega menu" and "Store Header v2"): the
 * top-level categories with their product counts, the top 8 brands of each with logos and the
 * popular filter chips, the header's featured categories (`featured_in_header`, by
 * `header_order`, at most 8) and the store's top brands. Built once per language and cached for
 * a few minutes; a category or brand change forgets it. URLs are built by the views.
 */
class StorefrontNavigationService
{
    public const int CACHE_SECONDS = 300;

    public const int MAX_FEATURED = 8;

    public const int BRANDS_PER_CATEGORY = 8;

    public function __construct(
        private StorefrontTaxonomyService $taxonomy,
        private ProductRepository $products,
        private ContentTranslator $translator,
        private MediaUrls $media,
    ) {}

    /**
     * @return array{total: int, categories: list<array<string, mixed>>, featured: list<array{slug: string, name: string}>, brands: list<array<string, mixed>>}
     */
    public function build(): array
    {
        return Cache::remember(self::cacheKey(app()->getLocale()), self::CACHE_SECONDS, fn (): array => $this->fresh());
    }

    /**
     * Forget the cached navigation of every language (a category or brand changed).
     */
    public static function forget(): void
    {
        foreach ((array) config('app.supported_locales', ['en', 'ar']) as $locale) {
            Cache::forget(self::cacheKey((string) $locale));
        }
    }

    private static function cacheKey(string $locale): string
    {
        return 'storefront:navigation:'.$locale;
    }

    /**
     * @return array{total: int, categories: list<array<string, mixed>>, featured: list<array{slug: string, name: string}>, brands: list<array<string, mixed>>}
     */
    private function fresh(): array
    {
        $categoryNodes = $this->taxonomy->categories();
        $platformNodes = $this->taxonomy->platforms();
        $this->translator->preload([...$categoryNodes->map(static fn (CatalogNode $node) => $node->model), ...$platformNodes->map(static fn (CatalogNode $node) => $node->model)], ['name']);

        $platforms = $platformNodes->keyBy(static fn (CatalogNode $node): int => $node->model->id);
        $topLevel = $categoryNodes->filter(static fn (CatalogNode $node): bool => $node->model->parent_id === null)->values();
        $ids = $topLevel->map(static fn (CatalogNode $node): int => $node->model->id)->all();

        $brandRows = collect($this->products->listedCountsByCategoryAnd($ids, 'gaming_platform_id'))->groupBy('category_id');
        $deliveryRows = collect($this->products->listedCountsByCategoryAnd($ids, 'delivery_type'))->groupBy('category_id');

        $categories = $topLevel->map(function (CatalogNode $node) use ($platforms, $brandRows, $deliveryRows): array {
            /** @var Category $category */
            $category = $node->model;
            $brands = collect($brandRows->get($category->id, []))
                ->filter(static fn (array $row): bool => $platforms->has((int) $row['value']))
                ->sortByDesc('count')
                ->values();

            return [
                'slug' => $category->slug,
                'name' => (string) $this->translator->text($category, 'name'),
                'count' => $node->productCount,
                'image' => $category->bannerMedia === null ? null : $this->media->image($category->bannerMedia),
                'brand_count' => $brands->count(),
                'brands' => $brands->take(self::BRANDS_PER_CATEGORY)->map(fn (array $row): array => $this->brand($platforms->get((int) $row['value'])->model, $row['count']))->all(),
                'delivery_types' => collect($deliveryRows->get($category->id, []))
                    ->filter(static fn (array $row): bool => DeliveryType::tryFrom((string) $row['value']) !== null)
                    ->sortByDesc('count')->take(2)->pluck('value')->map(static fn (mixed $value): string => (string) $value)->values()->all(),
                'featured_in_header' => $category->featured_in_header,
                'header_order' => $category->header_order,
            ];
        })->values();

        $featured = $categoryNodes
            ->filter(static fn (CatalogNode $node): bool => $node->model->featured_in_header)
            ->sortBy([static fn (CatalogNode $a, CatalogNode $b): int => [$a->model->header_order, $a->model->sort_order, $a->model->name] <=> [$b->model->header_order, $b->model->sort_order, $b->model->name]])
            ->take(self::MAX_FEATURED)
            ->map(fn (CatalogNode $node): array => ['slug' => $node->model->slug, 'name' => (string) $this->translator->text($node->model, 'name')])
            ->values()->all();

        $brands = $platformNodes
            ->filter(static fn (CatalogNode $node): bool => $node->productCount > 0)
            ->sortByDesc(static fn (CatalogNode $node): int => $node->productCount)
            ->take(16)
            ->map(fn (CatalogNode $node): array => $this->brand($node->model, $node->productCount))
            ->values()->all();

        return [
            'total' => $this->products->storefrontCount(ProductListingFilter::all()),
            'categories' => $categories->all(),
            'featured' => $featured,
            'brands' => $brands,
        ];
    }

    /**
     * @return array{slug: string, name: string, count: int, logo: array<string, mixed>|null}
     */
    private function brand(GamingPlatform $platform, int $count): array
    {
        return [
            'slug' => $platform->slug,
            'name' => (string) $this->translator->text($platform, 'name'),
            'count' => $count,
            'logo' => $platform->logoMedia === null ? null : $this->media->image($platform->logoMedia),
        ];
    }
}
