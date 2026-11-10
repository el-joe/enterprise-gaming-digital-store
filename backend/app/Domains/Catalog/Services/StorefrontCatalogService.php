<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\DTOs\VariantOffer;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Read side of the catalog for the storefront, priced in the visitor's display currency.
 */
class StorefrontCatalogService
{
    public function __construct(
        private ProductRepository $products,
        private CurrencyConverter $converter,
        private PricingEngine $engine,
        private ExchangeRateRegistry $registry,
    ) {}

    public function listing(ProductListingFilter $filter, string $displayCurrency): LengthAwarePaginator
    {
        $page = $this->products->storefrontPage($filter);
        $base = $this->registry->baseCurrency();

        $page->through(function (Product $product) use ($displayCurrency, $base): ProductCard {
            if ($product->price_from_base === null) {
                return new ProductCard($product, null, false);
            }

            $converted = $this->converter->toDisplay(Money::of($product->price_from_base, $base), $displayCurrency);

            return new ProductCard($product, $this->converter->present($converted->money), $converted->rate->isStale);
        });

        return $page;
    }

    /**
     * Convert a filter amount the customer typed in their display currency into base currency.
     */
    public function filterAmountToBase(?string $amount, string $displayCurrency): ?Money
    {
        return $amount === null ? null : $this->converter->toBase(Money::of($amount, $displayCurrency));
    }

    /**
     * @return Collection<int, VariantOffer>
     */
    public function variantOffers(Product $product, string $displayCurrency, ?string $country): Collection
    {
        if ($product->status !== ProductStatus::Active) {
            throw (new ModelNotFoundException)->setModel(Product::class);
        }

        $variants = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->with('region:id,code,name')
            ->orderBy('sort_order')
            ->get();

        $quotes = $this->engine->quoteMany($variants, new PricingContext($country, $displayCurrency));

        return $variants->map(function (ProductVariant $variant) use ($quotes): VariantOffer {
            $quote = $quotes[$variant->id];

            return new VariantOffer($variant, $quote, $quote->amount === null ? null : $this->converter->present($quote->amount));
        });
    }

    /**
     * @return array<int, array{code: string, name: string, symbol: string, exponent: int}>
     */
    public function currencies(): array
    {
        return array_map(
            static fn (array $currency): array => ['code' => $currency['code'], 'name' => $currency['name'], 'symbol' => $currency['symbol'], 'exponent' => $currency['exponent']],
            $this->registry->activeCurrencies(),
        );
    }
}
