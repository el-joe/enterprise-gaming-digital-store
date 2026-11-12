<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * Storefront listing criteria. Prices are already expressed in the base currency. `flag` is
 * `featured` or `trending` (home carousels, M-A4); the public listing never sets it.
 *
 * Storefront v2: every facet takes several values (`categories`, `platforms`, `regions`,
 * `deliveryTypes`, OR inside a facet, AND across facets); the single-value arguments stay for
 * the callers that pin one value and are merged in. `inStock` keeps variants in stock or
 * limited, `onSale` variants priced under their compare-at price, and `salesPeriodDays` ranks
 * best sellers by units paid in that many days (home rails).
 */
final readonly class ProductListingFilter
{
    /** Facets the facet counts leave out one at a time. */
    public const array FACETS = ['category', 'platform', 'region', 'delivery_type', 'price', 'in_stock'];

    /**
     * @param  list<string>  $categories
     * @param  list<string>  $platforms
     * @param  list<string>  $regions
     * @param  list<string>  $deliveryTypes
     */
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
        public ?string $category = null,
        public ?string $platform = null,
        public ?string $region = null,
        public ?string $deliveryType = null,
        public ?Money $minPriceBase = null,
        public ?Money $maxPriceBase = null,
        public string $sort = 'popular',
        public ?string $flag = null,
        public array $categories = [],
        public array $platforms = [],
        public array $regions = [],
        public array $deliveryTypes = [],
        public bool $inStock = false,
        public bool $onSale = false,
        public ?int $salesPeriodDays = null,
    ) {}

    /**
     * No filter: everything the storefront lists.
     */
    public static function all(): self
    {
        return new self;
    }

    /**
     * @return list<string>
     */
    public function categorySlugs(): array
    {
        return self::merge($this->categories, $this->category);
    }

    /**
     * @return list<string>
     */
    public function platformSlugs(): array
    {
        return self::merge($this->platforms, $this->platform);
    }

    /**
     * @return list<string>
     */
    public function regionCodes(): array
    {
        return self::merge($this->regions, $this->region);
    }

    /**
     * @return list<string>
     */
    public function deliveryTypeValues(): array
    {
        return self::merge($this->deliveryTypes, $this->deliveryType);
    }

    /**
     * The same criteria without one facet (`category`, `platform`, `region`, `delivery_type`,
     * `price` or `in_stock`), for "count with the other facets applied".
     */
    public function without(string $facet): self
    {
        return new self(
            page: 1,
            perPage: $this->perPage,
            categories: $facet === 'category' ? [] : $this->categorySlugs(),
            platforms: $facet === 'platform' ? [] : $this->platformSlugs(),
            regions: $facet === 'region' ? [] : $this->regionCodes(),
            deliveryTypes: $facet === 'delivery_type' ? [] : $this->deliveryTypeValues(),
            minPriceBase: $facet === 'price' ? null : $this->minPriceBase,
            maxPriceBase: $facet === 'price' ? null : $this->maxPriceBase,
            sort: $this->sort,
            flag: $this->flag,
            inStock: $facet === 'in_stock' ? false : $this->inStock,
            onSale: $this->onSale,
        );
    }

    /**
     * A stable key of everything that changes which products match (not the page or sort).
     */
    public function matchKey(): string
    {
        $sorted = static function (array $values): array {
            sort($values);

            return $values;
        };

        return sha1((string) json_encode([
            $sorted($this->categorySlugs()),
            $sorted($this->platformSlugs()),
            $sorted($this->regionCodes()),
            $sorted($this->deliveryTypeValues()),
            $this->minPriceBase?->amount,
            $this->maxPriceBase?->amount,
            $this->flag,
            $this->inStock,
            $this->onSale,
        ]));
    }

    /**
     * @param  list<string>  $values
     * @return list<string>
     */
    private static function merge(array $values, ?string $single): array
    {
        return array_values(array_unique(array_filter([...$values, $single], static fn (?string $value): bool => $value !== null && $value !== '')));
    }
}
