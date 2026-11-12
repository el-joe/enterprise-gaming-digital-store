<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Database\Eloquent\Collection;

/**
 * The image a product shows outside its own page (Storefront v2, owner request): bag lines,
 * checkout, orders, codes and the variant chooser, with the same priority as the cards:
 *
 * 1. the product's primary image (`product_images` by position): a staff upload or choice first,
 *    otherwise the active supplier image, which SupplierImageCandidates attaches as a product image;
 * 2. the brand (platform) logo;
 * 3. none: the view draws the generated dial art.
 *
 * load() eager-loads what resolve() reads, once per collection.
 */
class ProductImages
{
    public const string SOURCE_PRODUCT = 'product';

    public const string SOURCE_BRAND = 'brand';

    public function __construct(private MediaUrls $media) {}

    /**
     * @param  iterable<Product|null>  $products
     */
    public function load(iterable $products): void
    {
        $models = new Collection(array_values(array_filter(is_array($products) ? $products : iterator_to_array($products, false))));

        if ($models->isEmpty()) {
            return;
        }

        $models->loadMissing([
            'productImages' => static fn ($images) => $images->orderBy('sort_order')->orderBy('id'),
            'productImages.media',
            'gamingPlatform:id,name,slug,logo_media_id',
            'gamingPlatform.logoMedia',
        ]);
    }

    /**
     * @return array{source: string, image: array<string, mixed>}|null
     */
    public function resolve(?Product $product): ?array
    {
        if ($product === null) {
            return null;
        }

        $primary = $product->productImages->first(static fn ($image): bool => $image->media !== null);

        if ($primary !== null) {
            return ['source' => self::SOURCE_PRODUCT, 'image' => $this->media->image($primary->media, $primary->alt ?: $product->name)];
        }

        $logo = $product->gamingPlatform?->logoMedia;

        return $logo === null ? null : ['source' => self::SOURCE_BRAND, 'image' => $this->media->image($logo, $product->gamingPlatform->name)];
    }

    /**
     * The JSON fields of a line (additive): `image` (product image or brand logo, null for the
     * generated art) and `image_source` (`product`, `brand` or null).
     *
     * @return array{image: array<string, mixed>|null, image_source: string|null}
     */
    public function fields(?Product $product): array
    {
        $resolved = $this->resolve($product);

        return ['image' => $resolved['image'] ?? null, 'image_source' => $resolved['source'] ?? null];
    }
}
