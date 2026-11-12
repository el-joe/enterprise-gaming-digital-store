<?php

namespace App\Domains\Catalog\DTOs;

/**
 * Filter facets of a storefront listing (Storefront v2), in the sidebar order: category, brand
 * (platform), region, price range, delivery type and availability. Each option count is the
 * number of products the listing would show with that value and the other facets applied.
 * Facets a page fixes (the category on `/c/{slug}`, category and brand on `/b/{slug}`) are
 * absent. Price bounds are whole amounts in the display currency.
 */
final readonly class ListingFacets
{
    /**
     * @param  array<string, list<array{value: string, label: string, count: int, selected: bool, logo?: array<string, mixed>|null}>>  $options  keyed by facet (`category`, `platform`, `region`, `delivery_type`)
     * @param  array{currency: string, min: ?string, max: ?string, selected_min: ?string, selected_max: ?string}  $price
     * @param  array{count: int, selected: bool}  $inStock
     * @param  list<string>  $hidden
     */
    public function __construct(
        public array $options,
        public array $price,
        public array $inStock,
        public array $hidden,
        public int $total,
    ) {}

    /**
     * Number of selected values per facet, and in all.
     *
     * @return array<string, int>
     */
    public function selectedCounts(): array
    {
        $counts = [];

        foreach ($this->options as $facet => $options) {
            $counts[$facet] = count(array_filter($options, static fn (array $option): bool => $option['selected']));
        }

        $counts['price'] = (int) ($this->price['selected_min'] !== null || $this->price['selected_max'] !== null);
        $counts['in_stock'] = (int) $this->inStock['selected'];

        return $counts;
    }

    public function selectedTotal(): int
    {
        return array_sum($this->selectedCounts());
    }

    /**
     * The JSON shape (`/api/v1/storefront/catalog/facets`).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $facets = [];

        foreach (['category', 'platform', 'region', 'delivery_type'] as $facet) {
            if (array_key_exists($facet, $this->options)) {
                $facets[] = ['key' => $facet, 'type' => 'multi', 'options' => $this->options[$facet]];
            }

            if ($facet === 'region') {
                $facets[] = ['key' => 'price', 'type' => 'range', ...$this->price];
            }
        }

        $facets[] = ['key' => 'in_stock', 'type' => 'toggle', ...$this->inStock];

        return ['total' => $this->total, 'selected' => $this->selectedTotal(), 'facets' => $facets];
    }
}
