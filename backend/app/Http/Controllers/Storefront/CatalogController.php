<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\DTOs\DeliveryFieldSpec;
use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\VariantOffer;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\DeliveryFieldValidator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Catalog\Services\StorefrontFacetService;
use App\Domains\Cms\Services\MediaUrls;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\ResolvesDisplayCurrency;
use App\Http\Requests\Storefront\ProductListingRequest;
use App\Http\Requests\Storefront\ProductVariantsRequest;
use App\Http\Resources\Storefront\DeliveryFieldResource;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Resources\Storefront\ProductDetailResource;
use App\Http\Resources\Storefront\VariantOfferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use ResolvesDisplayCurrency;

    public function __construct(private StorefrontCatalogService $catalog, private ContentTranslator $translator) {}

    public function products(ProductListingRequest $request): JsonResponse
    {
        $currency = $this->displayCurrency($request);

        $page = $this->catalog->listing($request->listingFilter($currency, (int) $request->validated('per_page', 20)), $currency);

        $products = $page->getCollection()->map(static fn (ProductCard $card): Product => $card->product);
        $this->translator->preload([...$products, ...$products->map->gamingPlatform, ...$products->map->primaryCategory], ['name', 'short_description']);

        return $this->paginated($page, ProductCardResource::class, ['display_currency' => $currency]);
    }

    /**
     * Storefront v2 facets of the same criteria as products(): per facet value, the products the
     * listing would show with the other facets applied, and the price bounds in the display
     * currency. `hide=category,platform` leaves out the facets a category or brand page pins.
     */
    public function facets(ProductListingRequest $request, StorefrontFacetService $facets): JsonResponse
    {
        $currency = $this->displayCurrency($request);
        $hidden = array_values(array_intersect(StorefrontFacetService::HIDEABLE, explode(',', (string) $request->query('hide', ''))));
        $amount = static fn (mixed $value): ?string => is_string($value) && $value !== '' ? $value : null;

        $result = $facets->facets(
            $request->listingFilter($currency, 1),
            $currency,
            $hidden,
            typed: ['min' => $amount($request->validated('min_price')), 'max' => $amount($request->validated('max_price'))],
        );

        return $this->success($result->toArray(), meta: ['display_currency' => $currency]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $currency = $this->displayCurrency($request);

        $detail = $this->catalog->detail($slug, $currency);
        $product = $detail->card->product;

        $this->translator->preload([
            $product,
            $product->gamingPlatform,
            $product->primaryCategory,
            ...$product->regions,
            ...$detail->variantRegions,
            ...array_map(static fn (DeliveryFieldSpec $spec): DeliveryFieldDefinition => $spec->definition, $detail->deliveryFields),
        ], ['name', 'short_description', 'description', 'instructions', 'terms', 'label', 'help_text']);

        return $this->success(['product' => (new ProductDetailResource($detail))->resolve($request)], meta: ['display_currency' => $currency]);
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

    /**
     * Storefront v2 card "Add to bag" chooser: the product (name, image, brand logo), the delivery
     * fields the customer fills, and every active variant with its face value in its own currency,
     * region, selling price in the display currency and whether it can be bought now. Unavailable
     * variants are listed but marked `purchasable: false`.
     */
    public function quickAdd(ProductVariantsRequest $request, Product $product, DeliveryFieldValidator $fields, MediaUrls $media): JsonResponse
    {
        $currency = $this->displayCurrency($request);
        $offers = $this->catalog->variantOffers($product, $currency, $request->validated('country'));
        $specs = $fields->specsFor($product);
        $product->loadMissing(['productImages' => static fn ($images) => $images->orderBy('sort_order')->orderBy('id'), 'productImages.media', 'gamingPlatform.logoMedia']);

        $this->translator->preload([
            $product,
            $product->gamingPlatform,
            ...$offers->map(static fn (VariantOffer $offer): ProductVariant => $offer->variant),
            ...$offers->map(static fn (VariantOffer $offer) => $offer->variant->region)->filter(),
            ...array_map(static fn (DeliveryFieldSpec $spec): DeliveryFieldDefinition => $spec->definition, $specs),
        ], ['name', 'title', 'label', 'help_text']);

        $image = $product->productImages->first();

        return $this->success([
            'product' => [
                'name' => $this->translator->text($product, 'name'),
                'slug' => $product->slug,
                'image' => $image?->media === null ? null : $media->image($image->media, $image->alt),
                'platform' => [
                    'name' => $this->translator->text($product->gamingPlatform, 'name'),
                    'slug' => $product->gamingPlatform?->slug,
                    'logo' => $product->gamingPlatform?->logoMedia === null ? null : $media->image($product->gamingPlatform->logoMedia),
                ],
                'max_quantity' => (int) ($product->max_quantity_per_order ?: 10),
            ],
            'delivery_fields' => DeliveryFieldResource::collection($specs)->resolve(),
            'variants' => $offers->map(fn (VariantOffer $offer): array => [
                ...(new VariantOfferResource($offer))->resolve($request),
                // The cart refuses custom amounts for now (CartService::addItem).
                'purchasable' => $offer->quote->purchasable && ! $offer->variant->is_custom_amount && $offer->variant->availability !== ProductVariantAvailability::OutOfStock,
                'region_name' => $offer->variant->region === null ? null : $this->translator->text($offer->variant->region, 'name'),
            ])->values()->all(),
        ], meta: ['display_currency' => $currency]);
    }

    public function currencies(): JsonResponse
    {
        return $this->success(['currencies' => $this->catalog->currencies()]);
    }
}
