<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\DTOs\VariantOffer;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\ResolvesDisplayCurrency;
use App\Http\Requests\Storefront\ProductListingRequest;
use App\Http\Requests\Storefront\ProductVariantsRequest;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Resources\Storefront\VariantOfferResource;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    use ResolvesDisplayCurrency;

    public function __construct(private StorefrontCatalogService $catalog, private ContentTranslator $translator) {}

    public function products(ProductListingRequest $request): JsonResponse
    {
        $currency = $this->displayCurrency($request);

        $page = $this->catalog->listing(new ProductListingFilter(
            page: (int) $request->validated('page', 1),
            perPage: (int) $request->validated('per_page', 20),
            category: $request->validated('category'),
            platform: $request->validated('platform'),
            region: $request->validated('region'),
            deliveryType: $request->validated('delivery_type'),
            minPriceBase: $this->catalog->filterAmountToBase($request->validated('min_price'), $currency),
            maxPriceBase: $this->catalog->filterAmountToBase($request->validated('max_price'), $currency),
            sort: $request->validated('sort', 'popular'),
        ), $currency);

        $products = $page->getCollection()->map(static fn (ProductCard $card): Product => $card->product);
        $this->translator->preload([...$products, ...$products->map->gamingPlatform, ...$products->map->primaryCategory], ['name', 'short_description']);

        return $this->paginated($page, ProductCardResource::class, ['display_currency' => $currency]);
    }

    public function variants(ProductVariantsRequest $request, Product $product): JsonResponse
    {
        $currency = $this->displayCurrency($request);

        $offers = $this->catalog->variantOffers($product, $currency, $request->validated('country'));
        $this->translator->preload($offers->map(static fn (VariantOffer $offer): ProductVariant => $offer->variant), ['title']);

        return $this->success(
            ['variants' => VariantOfferResource::collection($offers)->resolve()],
            meta: ['display_currency' => $currency],
        );
    }

    public function currencies(): JsonResponse
    {
        return $this->success(['currencies' => $this->catalog->currencies()]);
    }
}
