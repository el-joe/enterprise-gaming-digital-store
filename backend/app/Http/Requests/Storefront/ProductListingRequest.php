<?php

namespace App\Http\Requests\Storefront;

use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Listing criteria of the storefront (web pages and `/api/v1[/mobile]/storefront/catalog`).
 *
 * Storefront v2: `category`, `platform` (brand), `region` and `delivery_type` take one value, a
 * comma-separated list (`?platform=steam,xbox`) or a list (`?platform[]=steam`); `in_stock=1`
 * and `on_sale=1` narrow to matching variants; `sort=best_selling` is the name of `popular`.
 */
class ProductListingRequest extends FormRequest
{
    /** Facets that take several values. */
    public const array LIST_FILTERS = ['category', 'platform', 'region', 'delivery_type'];

    public const array SORTS = ['best_selling', 'popular', 'newest', 'price_asc', 'price_desc', 'name'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'category' => ['sometimes', 'array', 'max:20'],
            'category.*' => ['string', 'max:140'],
            'platform' => ['sometimes', 'array', 'max:40'],
            'platform.*' => ['string', 'max:140'],
            'region' => ['sometimes', 'array', 'max:40'],
            'region.*' => ['string', 'max:16'],
            'delivery_type' => ['sometimes', 'array', 'max:6'],
            'delivery_type.*' => [Rule::enum(DeliveryType::class)],
            'min_price' => ['sometimes', DecimalString::money()],
            'max_price' => ['sometimes', DecimalString::money()],
            'in_stock' => ['sometimes', 'boolean'],
            'on_sale' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', Rule::in(self::SORTS)],
        ];
    }

    /**
     * One value, a comma list or a list become a list without blanks or duplicates.
     */
    protected function prepareForValidation(): void
    {
        $lists = [];

        foreach (self::LIST_FILTERS as $key) {
            if (! $this->has($key)) {
                continue;
            }

            $value = $this->input($key);
            $values = is_array($value) ? $value : explode(',', (string) $value);
            $lists[$key] = array_values(array_unique(array_filter(array_map(
                static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
                $values,
            ), static fn (string $item): bool => $item !== '')));
        }

        $this->merge($lists);
    }

    /**
     * Selected values of a list facet.
     *
     * @return list<string>
     */
    public function selected(string $key): array
    {
        return array_values((array) ($this->validated($key) ?? []));
    }

    public function sortValue(string $default = 'popular'): string
    {
        $sort = $this->validated('sort');

        return is_string($sort) && $sort !== '' ? $sort : $default;
    }

    /**
     * The criteria for the listing service. `$fixed` pins a category or brand (its page) and
     * replaces whatever the query asks for that facet. Price bounds convert from the display
     * currency to base at the current rate.
     *
     * @param  array{category?: string, platform?: string}  $fixed
     */
    public function listingFilter(string $displayCurrency, int $perPage, array $fixed = [], string $defaultSort = 'popular'): ProductListingFilter
    {
        $catalog = app(StorefrontCatalogService::class);
        $amount = fn (string $key): ?string => is_string($value = $this->validated($key)) && $value !== '' ? $value : null;

        return new ProductListingFilter(
            page: (int) ($this->validated('page') ?? 1),
            perPage: $perPage,
            minPriceBase: $catalog->filterAmountToBase($amount('min_price'), $displayCurrency),
            maxPriceBase: $catalog->filterAmountToBase($amount('max_price'), $displayCurrency),
            sort: $this->sortValue($defaultSort),
            categories: isset($fixed['category']) ? [$fixed['category']] : $this->selected('category'),
            platforms: isset($fixed['platform']) ? [$fixed['platform']] : $this->selected('platform'),
            regions: $this->selected('region'),
            deliveryTypes: $this->selected('delivery_type'),
            inStock: $this->boolean('in_stock'),
            onSale: $this->boolean('on_sale'),
        );
    }
}
