<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * Storefront listing criteria. Prices are already expressed in the base currency. `flag` is
 * `featured` or `trending` (home carousels, M-A4); the public listing never sets it.
 */
final readonly class ProductListingFilter
{
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
    ) {}

    /**
     * No filter: everything the storefront lists.
     */
    public static function all(): self
    {
        return new self;
    }
}
