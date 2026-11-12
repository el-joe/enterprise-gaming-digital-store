<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\ListingFacets;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Region;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Facets of the storefront listing (Storefront v2, docs/STOREFRONT_IA.md): for each facet, the
 * number of products per value with every other facet applied (grouped counts on active
 * products with a sellable variant, ProductRepository). Counts are cached briefly per filter
 * hash; names, logos and the display-currency price bounds are resolved per request.
 */
class StorefrontFacetService
{
    public const int CACHE_SECONDS = 60;

    /** Every facet a page can hide. */
    public const array HIDEABLE = ['category', 'platform'];

    public function __construct(
        private ProductRepository $products,
        private ContentTranslator $translator,
        private MediaUrls $media,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $registry,
    ) {}

    /**
     * @param  list<string>  $hidden  facets the page fixes (`category`, `platform`)
     * @param  int|null  $total  the listing total when the caller already has it
     * @param  array{min?: ?string, max?: ?string}  $typed  the price bounds as the shopper typed them (display currency)
     */
    public function facets(ProductListingFilter $filter, string $displayCurrency, array $hidden = [], ?int $total = null, array $typed = []): ListingFacets
    {
        $counts = Cache::remember('storefront:facets:'.$filter->matchKey(), self::CACHE_SECONDS, fn (): array => [
            'category' => in_array('category', $hidden, true) ? [] : $this->products->facetCountsByCategory($filter),
            'platform' => in_array('platform', $hidden, true) ? [] : $this->products->facetCountsByPlatform($filter),
            'region' => $this->products->facetCountsByRegion($filter),
            'delivery_type' => $this->products->facetCountsByDeliveryType($filter),
            'in_stock' => $this->products->facetInStockCount($filter),
            'price' => $this->products->facetPriceBounds($filter),
            'total' => $this->products->storefrontCount($filter),
        ]);

        $options = [];

        if (! in_array('category', $hidden, true)) {
            $options['category'] = $this->categoryOptions($counts['category'], $filter->categorySlugs());
        }

        if (! in_array('platform', $hidden, true)) {
            $options['platform'] = $this->platformOptions($counts['platform'], $filter->platformSlugs());
        }

        $options['region'] = $this->regionOptions($counts['region'], $filter->regionCodes());
        $options['delivery_type'] = $this->deliveryOptions($counts['delivery_type'], $filter->deliveryTypeValues());

        return new ListingFacets(
            options: $options,
            price: $this->price($counts['price'], $filter, $displayCurrency, $typed),
            inStock: ['count' => (int) $counts['in_stock'], 'selected' => $filter->inStock],
            hidden: array_values(array_intersect(self::HIDEABLE, $hidden)),
            total: $total ?? (int) $counts['total'],
        );
    }

    /**
     * @param  array<int, int>  $counts
     * @param  list<string>  $selected
     * @return list<array{value: string, label: string, count: int, selected: bool}>
     */
    private function categoryOptions(array $counts, array $selected): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->where(static fn (Builder $query) => $query->whereNull('parent_id')->orWhereHas('parent', static fn (Builder $parent) => $parent->where('is_active', true)))
            ->where(static fn (Builder $query) => $query->whereIn('id', array_keys($counts))->orWhereIn('slug', $selected))
            ->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'parent_id']);

        $this->translator->preload($categories, ['name']);

        return $categories->map(fn (Category $category): array => [
            'value' => $category->slug,
            'label' => (string) $this->translator->text($category, 'name'),
            'count' => $counts[$category->id] ?? 0,
            'selected' => in_array($category->slug, $selected, true),
        ])->values()->all();
    }

    /**
     * Brands by number of products, then name.
     *
     * @param  array<int, int>  $counts
     * @param  list<string>  $selected
     * @return list<array{value: string, label: string, count: int, selected: bool, logo: array<string, mixed>|null}>
     */
    private function platformOptions(array $counts, array $selected): array
    {
        $platforms = GamingPlatform::query()
            ->where('is_active', true)
            ->where(static fn (Builder $query) => $query->whereIn('id', array_keys($counts))->orWhereIn('slug', $selected))
            ->with('logoMedia')
            ->get(['id', 'name', 'slug', 'logo_media_id']);

        $this->translator->preload($platforms, ['name']);

        return $platforms->map(fn (GamingPlatform $platform): array => [
            'value' => $platform->slug,
            'label' => (string) $this->translator->text($platform, 'name'),
            'count' => $counts[$platform->id] ?? 0,
            'selected' => in_array($platform->slug, $selected, true),
            'logo' => $platform->logoMedia === null ? null : $this->media->image($platform->logoMedia),
        ])->sort(static fn (array $a, array $b): int => [$b['count'], $a['label']] <=> [$a['count'], $b['label']])->values()->all();
    }

    /**
     * Regions (variant region code) by number of products, the global region first among ties.
     *
     * @param  array<int, int>  $counts
     * @param  list<string>  $selected
     * @return list<array{value: string, label: string, count: int, selected: bool}>
     */
    private function regionOptions(array $counts, array $selected): array
    {
        $regions = Region::query()
            ->where(static fn (Builder $query) => $query->whereIn('id', array_keys($counts))->orWhereIn('code', $selected))
            ->get(['id', 'code', 'name', 'is_global']);

        $this->translator->preload($regions, ['name']);

        return $regions->map(fn (Region $region): array => [
            'value' => $region->code,
            'label' => (string) ($this->translator->text($region, 'name') ?: $region->name),
            'count' => $counts[$region->id] ?? 0,
            'selected' => in_array($region->code, $selected, true),
            'global' => $region->is_global,
        ])->sort(static fn (array $a, array $b): int => [$b['count'], $b['global'], $a['label']] <=> [$a['count'], $a['global'], $b['label']])
            ->map(static fn (array $option): array => array_diff_key($option, ['global' => true]))
            ->values()->all();
    }

    /**
     * @param  array<string, int>  $counts
     * @param  list<string>  $selected
     * @return list<array{value: string, label: string, count: int, selected: bool}>
     */
    private function deliveryOptions(array $counts, array $selected): array
    {
        $options = [];

        foreach (DeliveryType::cases() as $type) {
            $count = $counts[$type->value] ?? 0;
            $isSelected = in_array($type->value, $selected, true);

            if ($count > 0 || $isSelected) {
                $options[] = ['value' => $type->value, 'label' => (string) __('storefront.delivery.'.$type->value), 'count' => $count, 'selected' => $isSelected];
            }
        }

        usort($options, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return $options;
    }

    /**
     * Whole display-currency bounds around the matching variants (floor of the lowest, ceiling of
     * the highest) and the selected bounds as typed (display currency).
     *
     * @param  array{min: ?string, max: ?string}  $bounds
     * @param  array{min?: ?string, max?: ?string}  $typed
     * @return array{currency: string, min: ?string, max: ?string, selected_min: ?string, selected_max: ?string}
     */
    private function price(array $bounds, ProductListingFilter $filter, string $displayCurrency, array $typed): array
    {
        $base = $this->registry->baseCurrency();
        $display = fn (?string $amount, bool $up): ?string => $amount === null ? null : self::whole($this->converter->toDisplay(Money::of($amount, $base), $displayCurrency)->money->amount, $up);
        $selected = fn (?Money $amount): ?string => $amount === null ? null : self::whole($this->converter->toDisplay($amount, $displayCurrency)->money->amount, false, true);

        return [
            'currency' => $displayCurrency,
            'min' => $display($bounds['min'], false),
            'max' => $display($bounds['max'], true),
            'selected_min' => $typed['min'] ?? $selected($filter->minPriceBase),
            'selected_max' => $typed['max'] ?? $selected($filter->maxPriceBase),
        ];
    }

    /**
     * A non-negative decimal string rounded down (or up) to a whole number, or rounded to the
     * nearest whole number for a bound the shopper typed.
     */
    private static function whole(string $amount, bool $up, bool $nearest = false): string
    {
        $integer = bcadd($amount, '0', 0);
        $fraction = bcsub($amount, $integer, 6);

        if ($nearest) {
            return bccomp($fraction, '0.5', 6) >= 0 ? bcadd($integer, '1', 0) : $integer;
        }

        return $up && bccomp($fraction, '0', 6) === 1 ? bcadd($integer, '1', 0) : $integer;
    }
}
