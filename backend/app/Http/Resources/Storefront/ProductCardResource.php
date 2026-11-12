<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\FaceValueFormatter;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ProductCard $resource
 */
class ProductCardResource extends JsonResource
{
    /**
     * Transform the resource into an array. Names and the short description are in the request
     * locale, with the English base column as the fallback (ContentTranslator). `image.path` is
     * kept for existing clients; `image.url` is absolute (M-A4, MediaUrls). `platform.logo` is the
     * platform's active logo (staff upload or supplier brand logo), null without one.
     *
     * Storefront v2 (additive): `face_value` is the gift card's value in its own currency
     * (FaceValueFormatter), never converted; `quick_add` says how the card's bag button adds:
     * `variant_id` when exactly one variant is purchasable and no delivery field is required,
     * otherwise the client opens the variant chooser (`/catalog/products/{slug}/quick-add`).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->resource->product;
        $translator = app(ContentTranslator::class);

        /** @var ProductImage|null $image */
        $image = $product->productImages->first();

        return [
            'public_id' => $product->public_id,
            'name' => $translator->text($product, 'name'),
            'slug' => $product->slug,
            'short_description' => $translator->text($product, 'short_description'),
            'delivery_type' => $product->delivery_type->value,
            'platform' => [
                'name' => $translator->text($product->gamingPlatform, 'name'),
                'slug' => $product->gamingPlatform?->slug,
                'logo' => $product->gamingPlatform?->logoMedia === null ? null : app(MediaUrls::class)->image($product->gamingPlatform->logoMedia, $product->gamingPlatform->name),
            ],
            'category' => ['name' => $translator->text($product->primaryCategory, 'name'), 'slug' => $product->primaryCategory?->slug],
            'image' => $image?->media === null ? null : app(MediaUrls::class)->image($image->media, $image->alt),
            'price_from' => $this->resource->priceFrom,
            'price_may_vary' => $this->resource->priceMayVary,
            'is_featured' => $product->is_featured,
            'rating_avg' => $product->rating_avg,
            'face_value' => app(FaceValueFormatter::class)->card(
                self::decimal($product->getAttribute('face_min')),
                self::decimal($product->getAttribute('face_max')),
                $product->getAttribute('face_currency'),
                (int) $product->getAttribute('face_currencies'),
                self::decimal($product->getAttribute('custom_min')),
                self::decimal($product->getAttribute('custom_max')),
                $product->getAttribute('custom_currency'),
            ),
            'quick_add' => [
                'variant_count' => (int) $product->getAttribute('purchasable_variants'),
                'variant_id' => (int) $product->getAttribute('purchasable_variants') === 1 && (int) $product->getAttribute('required_fields') === 0 ? $product->getAttribute('quick_variant') : null,
                'requires_fields' => (int) $product->getAttribute('required_fields') > 0,
            ],
        ];
    }

    private static function decimal(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
