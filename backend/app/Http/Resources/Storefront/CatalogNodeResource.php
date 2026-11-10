<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A storefront platform or category (M-A4). Images are null unless an admin uploaded one;
 * no official brand logo is supplied by the platform (BD8).
 *
 * @property-read CatalogNode $resource
 */
class CatalogNodeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $model = $this->resource->model;
        $translator = app(ContentTranslator::class);
        $media = app(MediaUrls::class);
        $image = static fn (?Media $file): ?array => $file === null ? null : $media->image($file);

        $common = [
            'name' => $translator->text($model, 'name'),
            'slug' => $model->slug,
            'description' => $translator->text($model, 'description'),
            'product_count' => $this->resource->productCount,
        ];

        if ($model instanceof Category) {
            return [...$common, 'parent' => $model->parent?->slug, 'banner' => $image($model->bannerMedia)];
        }

        return [...$common, 'is_featured' => $model->is_featured, 'logo' => $image($model->logoMedia), 'banner' => $image($model->bannerMedia)];
    }
}
