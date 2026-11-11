<?php

namespace App\Http\Controllers\Web;

use App\Domains\Analytics\Services\PageAnalytics;
use App\Domains\Catalog\DTOs\DeliveryFieldSpec;
use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\DTOs\VariantOffer;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Catalog\Services\WishlistService;
use App\Domains\Payments\Services\PaymentPublicCredentials;
use App\Domains\Reviews\Repositories\ReviewRepository;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Domains\Seo\StructuredData\StructuredDataBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Resources\Storefront\ProductDetailResource;
use App\Http\Resources\Storefront\ProductReviewResource;
use App\Http\Resources\Storefront\VariantOfferResource;
use App\Http\Support\LocalizedRoutes;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Product page (`/products/{slug}`): the product JSON (M-A4), its variants with prices in the
 * display currency, approved reviews and related products. Adding to the bag posts to
 * `POST /api/v1/storefront/cart/items`, which validates the delivery fields shown here.
 * M18: SEO values through SeoResolver, Product JSON-LD with the rendered offers and the browser
 * `view_item` event, both from the same server prices.
 */
class ProductController extends Controller
{
    private const int REVIEWS_SHOWN = 6;

    private const int RELATED_SHOWN = 4;

    public function __construct(
        private StorefrontCatalogService $catalog,
        private ContentTranslator $translator,
        private SeoResolver $seo,
        private PageSchema $schema,
        private StructuredDataBuilder $structuredData,
        private PageAnalytics $analytics,
    ) {}

    public function show(Request $request, string $slug, StorefrontRequest $storefront, ReviewRepository $reviews, PaymentPublicCredentials $gateways, WishlistService $wishlist): View
    {
        $currency = $storefront->currency($request);
        $detail = $this->catalog->detail($slug, $currency);
        $product = $detail->card->product;

        $offers = $this->catalog->variantOffers($product, $currency, null);

        $this->translator->preload([
            $product,
            $product->gamingPlatform,
            $product->primaryCategory,
            ...$product->regions,
            ...$detail->variantRegions,
            ...array_map(static fn (DeliveryFieldSpec $spec): DeliveryFieldDefinition => $spec->definition, $detail->deliveryFields),
            ...$offers->map(static fn (VariantOffer $offer): ProductVariant => $offer->variant),
        ], ['name', 'short_description', 'description', 'instructions', 'terms', 'label', 'help_text', 'title']);

        $productData = (new ProductDetailResource($detail))->resolve($request);
        $variantData = VariantOfferResource::collection($offers)->resolve($request);

        $seo = $this->seo->resolve(SeoTemplatePageType::Product, $product, [
            'name' => $productData['name'],
            'platform' => $productData['platform']['name'] ?? null,
            'category' => $productData['category']['name'] ?? null,
            'region' => $productData['regions'][0]['name'] ?? null,
            'min_price' => $productData['price_from']['formatted'] ?? null,
        ], (string) $productData['name'], $productData['short_description'] ?? null);

        $url = LocalizedRoutes::url('storefront.products.show', ['slug' => $product->slug]);
        $this->schema->add($this->structuredData->product($productData, $variantData, $url, app()->getLocale()));
        $this->analytics->viewItem($productData, $variantData);

        return view('storefront.product', [
            'product' => $productData,
            'variants' => $variantData,
            'seo' => $seo,
            'reviews' => ProductReviewResource::collection($reviews->approvedFor($product->id, 1, self::REVIEWS_SHOWN)->getCollection())->resolve($request),
            'related' => $this->related($product, $currency, $request),
            'paymentMethods' => array_keys($gateways->forActiveGateways()),
            'saved' => $request->user() !== null && $wishlist->contains($request->user(), $product->id),
        ]);
    }

    /**
     * Other listed products of the same category (else platform), priced like the listing.
     *
     * @return list<array<string, mixed>>
     */
    private function related(Product $product, string $currency, Request $request): array
    {
        $filter = new ProductListingFilter(
            perPage: self::RELATED_SHOWN + 1,
            category: $product->primaryCategory?->slug,
            platform: $product->primaryCategory === null ? $product->gamingPlatform?->slug : null,
        );

        $cards = $this->catalog->listing($filter, $currency)->getCollection()
            ->reject(static fn (ProductCard $card): bool => $card->product->id === $product->id)
            ->take(self::RELATED_SHOWN)
            ->values();

        $products = $cards->map(static fn (ProductCard $card): Product => $card->product);
        $this->translator->preload([...$products, ...$products->map->gamingPlatform, ...$products->map->primaryCategory], ['name', 'short_description']);

        return ProductCardResource::collection($cards)->resolve($request);
    }
}
