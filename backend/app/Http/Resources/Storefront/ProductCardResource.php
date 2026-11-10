<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Services\ContentTranslator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ProductCard $resource
 */
class ProductCardResource extends JsonResource
{
    /**
     * Transform the resource into an array. Names and the short description are in the request
     * locale, with the English base column as the fallback (ContentTranslator).
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
            'platform' => ['name' => $translator->text($product->gamingPlatform, 'name'), 'slug' => $product->gamingPlatform?->slug],
            'category' => ['name' => $translator->text($product->primaryCategory, 'name'), 'slug' => $product->primaryCategory?->slug],
            'image' => $image?->media === null ? null : ['path' => $image->media->path, 'alt' => $image->alt ?? $image->media->alt],
            'price_from' => $this->resource->priceFrom,
            'price_may_vary' => $this->resource->priceMayVary,
            'is_featured' => $product->is_featured,
            'rating_avg' => $product->rating_avg,
        ];
    }
}
