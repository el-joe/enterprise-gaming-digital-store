<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductDetail;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\DTOs\VariantOffer;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
        private DeliveryFieldValidator $deliveryFields,
    ) {}

    public function listing(ProductListingFilter $filter, string $displayCurrency): LengthAwarePaginator
    {
        $page = $this->products->storefrontPage($filter);
        $base = $this->registry->baseCurrency();

        $page->through(fn (Product $product): ProductCard => $this->card($product, $displayCurrency, $base));

        return $page;
    }

    /**
     * Every listed product (id, slug, update time) for the sitemap (M18).
     *
     * @return Builder<Product>
     */
    public function sitemapProducts(): Builder
    {
        return $this->products->listedForSitemap();
    }

    /**
     * The first listed products for a filter, unpriced and without relations; price them with
     * cards() (home carousels, M-A4).
     *
     * @return EloquentCollection<int, Product>
     */
    public function topProducts(ProductListingFilter $filter, int $limit): EloquentCollection
    {
        return $this->products->storefrontTop($filter, $limit);
    }

    /**
     * Listed products among these ids, keyed by id, unpriced and without relations.
     *
     * @param  list<int>  $ids
     * @return EloquentCollection<int, Product>
     */
    public function listedProducts(array $ids): EloquentCollection
    {
        return $this->products->storefrontByIds($ids);
    }

    /**
     * Cards for products loaded with `price_from_base` (ProductRepository), priced exactly as the
     * listing prices them. Card relations are loaded once for the whole collection.
     *
     * @param  EloquentCollection<int, Product>  $products
     * @return Collection<int, ProductCard>
     */
    public function cards(EloquentCollection $products, string $displayCurrency): Collection
    {
        $products->loadMissing(ProductRepository::cardRelations());
        $base = $this->registry->baseCurrency();

        return $products->values()->toBase()->map(fn (Product $product): ProductCard => $this->card($product, $displayCurrency, $base));
    }

    /**
     * Cards of listed products by id, in the given order, priced like the listing (wishlist,
     * M13). Ids of products that are no longer listed are skipped.
     *
     * @param  list<int>  $ids
     * @return Collection<int, ProductCard>
     */
    public function cardsForIds(array $ids, string $displayCurrency): Collection
    {
        return $this->cards($this->products->storefrontPricedByIds($ids), $displayCurrency);
    }

    /**
     * An active product with its card price, delivery fields, variant summary and SEO metadata.
     *
     * @throws ModelNotFoundException
     */
    public function detail(string $slug, string $displayCurrency): ProductDetail
    {
        $product = $this->products->storefrontDetail($slug) ?? throw (new ModelNotFoundException)->setModel(Product::class);

        $variants = $product->productVariants;
        $availability = null;

        foreach ([ProductVariantAvailability::InStock, ProductVariantAvailability::Limited, ProductVariantAvailability::OutOfStock] as $candidate) {
            if ($variants->contains(static fn (ProductVariant $variant): bool => $variant->availability === $candidate)) {
                $availability = $candidate;

                break;
            }
        }

        $seo = SeoMetadata::query()
            ->with('ogImageMedia')
            ->where('seoable_type', $product->getMorphClass())
            ->where('seoable_id', $product->id)
            ->whereIn('locale', array_unique([app()->getLocale(), (string) config('app.fallback_locale')]))
            ->get()
            ->sortBy(static fn (SeoMetadata $row): int => $row->locale === app()->getLocale() ? 0 : 1)
            ->first();

        return new ProductDetail(
            card: $this->card($product, $displayCurrency, $this->registry->baseCurrency()),
            deliveryFields: $this->deliveryFields->specsFor($product),
            variantCount: $variants->count(),
            availability: $availability,
            hasCustomAmount: $variants->contains(static fn (ProductVariant $variant): bool => $variant->is_custom_amount),
            variantRegions: $variants->pluck('region')->filter()->unique('id')->values()->all(),
            seo: $seo,
        );
    }

    private function card(Product $product, string $displayCurrency, string $base): ProductCard
    {
        if ($product->price_from_base === null) {
            return new ProductCard($product, null, false);
        }

        $converted = $this->converter->toDisplay(Money::of($product->price_from_base, $base), $displayCurrency);

        return new ProductCard($product, $this->converter->present($converted->money), $converted->rate->isStale);
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
