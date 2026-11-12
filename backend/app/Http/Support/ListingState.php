<?php

namespace App\Http\Support;

use App\Domains\Catalog\DTOs\ListingFacets;
use App\Http\Requests\Storefront\ProductListingRequest;

/**
 * The filters of a storefront listing page as URL state (Storefront v2): lists are written
 * comma-separated (`?platform=steam,xbox&in_stock=1`), so a page can be shared and crawled.
 * Builds the applied-filter chips with their remove links, the "Clear all" link and the SEO
 * filter count (SEO_STRATEGY.md section 2: one filter or a sort canonicalizes to the plain
 * page; two or more are `noindex,follow`).
 */
final class ListingState
{
    public const string DEFAULT_SORT = 'best_selling';

    /**
     * @param  array<string, list<string>>  $lists  category, platform, region, delivery_type
     */
    public function __construct(
        public readonly string $baseUrl,
        public readonly array $lists,
        public readonly ?string $minPrice,
        public readonly ?string $maxPrice,
        public readonly bool $inStock,
        public readonly bool $onSale,
        public readonly string $sort,
    ) {}

    /**
     * @param  list<string>  $fixed  facets the page pins (they never appear in its URL)
     */
    public static function fromRequest(ProductListingRequest $request, string $baseUrl, array $fixed = []): self
    {
        $lists = [];

        foreach (ProductListingRequest::LIST_FILTERS as $key) {
            $lists[$key] = in_array($key, $fixed, true) ? [] : $request->selected($key);
        }

        $amount = static fn (mixed $value): ?string => is_string($value) && $value !== '' ? $value : null;
        $sort = $request->sortValue(self::DEFAULT_SORT);

        return new self(
            $baseUrl,
            $lists,
            $amount($request->validated('min_price')),
            $amount($request->validated('max_price')),
            $request->boolean('in_stock'),
            $request->boolean('on_sale'),
            $sort === 'popular' ? self::DEFAULT_SORT : $sort,
        );
    }

    /**
     * The URL with these changes (`null` or `[]` removes a key), always back on page 1.
     *
     * @param  array<string, mixed>  $changes
     */
    public function url(array $changes = []): string
    {
        $values = [
            ...$this->lists,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'in_stock' => $this->inStock ? '1' : null,
            'on_sale' => $this->onSale ? '1' : null,
            'sort' => $this->sort === self::DEFAULT_SORT ? null : $this->sort,
            ...$changes,
        ];

        $parts = [];

        foreach (['category', 'platform', 'region', 'delivery_type', 'min_price', 'max_price', 'in_stock', 'on_sale', 'sort'] as $key) {
            $value = $values[$key] ?? null;

            if (is_array($value)) {
                $value = $value === [] ? null : implode(',', array_map('rawurlencode', $value));
            } elseif ($value !== null) {
                $value = rawurlencode((string) $value);
            }

            if ($value !== null && $value !== '') {
                $parts[] = $key.'='.$value;
            }
        }

        return $parts === [] ? $this->baseUrl : $this->baseUrl.'?'.implode('&', $parts);
    }

    /**
     * A page of the current filters (page 1 has no `page` parameter).
     */
    public function pageUrl(int $page): string
    {
        $url = $this->url();

        return $page <= 1 ? $url : $url.(str_contains($url, '?') ? '&' : '?').'page='.$page;
    }

    public function clearUrl(): string
    {
        return $this->baseUrl.($this->sort === self::DEFAULT_SORT ? '' : '?sort='.rawurlencode($this->sort));
    }

    /**
     * Filters in force, each counted once per selected value (the sort counts as one too).
     */
    public function filterCount(): int
    {
        return array_sum(array_map('count', $this->lists))
            + (int) ($this->minPrice !== null) + (int) ($this->maxPrice !== null)
            + (int) $this->inStock + (int) $this->onSale
            + (int) ($this->sort !== self::DEFAULT_SORT);
    }

    /**
     * Applied-filter chips: label and the URL without that value.
     *
     * @return list<array{label: string, url: string}>
     */
    public function chips(ListingFacets $facets): array
    {
        $chips = [];

        foreach ($this->lists as $key => $values) {
            $labels = array_column($facets->options[$key] ?? [], 'label', 'value');

            foreach ($values as $value) {
                $chips[] = [
                    'label' => (string) ($labels[$value] ?? ($key === 'delivery_type' ? __('storefront.delivery.'.$value) : $value)),
                    'url' => $this->url([$key => array_values(array_diff($values, [$value]))]),
                ];
            }
        }

        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $chips[] = [
                'label' => (string) __('storefront.catalog.price_chip', ['min' => $this->minPrice ?? '0', 'max' => $this->maxPrice ?? '∞', 'currency' => $facets->price['currency']]),
                'url' => $this->url(['min_price' => null, 'max_price' => null]),
            ];
        }

        if ($this->inStock) {
            $chips[] = ['label' => (string) __('storefront.catalog.in_stock'), 'url' => $this->url(['in_stock' => null])];
        }

        if ($this->onSale) {
            $chips[] = ['label' => (string) __('storefront.catalog.on_sale'), 'url' => $this->url(['on_sale' => null])];
        }

        return $chips;
    }
}
