<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\ProductDetail;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\Region;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Shared\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The storefront product page (M-A4): the listing card plus long content, gallery, delivery
 * fields, a variant summary and SEO values. `description` is HTML limited to the HtmlSanitizer
 * allow-list (sanitized again on output, because translations are not written through the
 * product service); `instructions` and `terms` are plain text to be shown escaped. Cost,
 * supplier and margin data never appear.
 *
 * @property-read ProductDetail $resource
 */
class ProductDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $detail = $this->resource;
        $product = $detail->card->product;
        $translator = app(ContentTranslator::class);
        $media = app(MediaUrls::class);
        $seo = $detail->seo;

        $region = static fn (Region $region): array => ['code' => $region->code, 'name' => $translator->text($region, 'name')];

        return [
            ...(new ProductCardResource($detail->card))->toArray($request),
            'sku' => $product->sku,
            'description' => app(HtmlSanitizer::class)->sanitize($translator->text($product, 'description')),
            'instructions' => $translator->text($product, 'instructions'),
            'terms' => $translator->text($product, 'terms'),
            'delivery_time_minutes' => $product->delivery_time_minutes,
            'max_quantity_per_order' => $product->max_quantity_per_order,
            'images' => $product->productImages
                ->filter(static fn (ProductImage $image): bool => $image->media !== null)
                ->map(static fn (ProductImage $image): array => $media->image($image->media, $image->alt))
                ->values()
                ->all(),
            'regions' => $product->regions->map($region)->values()->all(),
            'rating' => ['average' => (string) $product->rating_avg, 'count' => (int) $product->rating_count],
            'availability' => $detail->availability?->value,
            'variants_summary' => [
                'count' => $detail->variantCount,
                'regions' => array_map($region, $detail->variantRegions),
                'has_custom_amount' => $detail->hasCustomAmount,
            ],
            'delivery_fields' => DeliveryFieldResource::collection($detail->deliveryFields)->resolve(),
            'seo' => [
                'title' => $seo?->title ?? $translator->text($product, 'name'),
                'description' => $seo?->description ?? $translator->text($product, 'short_description'),
                'robots' => $seo?->robots,
                'image' => $seo?->ogImageMedia === null ? null : $media->image($seo->ogImageMedia)['url'],
            ],
        ];
    }
}
