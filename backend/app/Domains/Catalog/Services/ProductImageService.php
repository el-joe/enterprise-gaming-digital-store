<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * @extends CrudService<ProductImage>
 */
class ProductImageService extends CrudService
{
    public function listFor(Product $product, ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            ProductImage::query()->with($this->relations())->where('product_id', $product->id),
            $list,
            sorts: ['id', 'sort_order'],
            defaultSort: 'sort_order',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createFor(Product $product, array $attributes): ProductImage
    {
        return $this->create([...$attributes, 'product_id' => $product->id]);
    }

    /**
     * A staff member adds an image: it always wins over a synced supplier image (owner decision
     * 12), so the product's supplier images are detached; the first image is the primary one.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function addByStaff(Product $product, array $attributes): ProductImage
    {
        return DB::transaction(function () use ($product, $attributes): ProductImage {
            $media = Media::query()->find($attributes['media_id'] ?? null);

            if ($media !== null && $media->source !== MediaSource::Supplier) {
                ProductImage::query()->where('product_id', $product->id)
                    ->whereIn('media_id', Media::query()->where('source', MediaSource::Supplier)->select('id'))
                    ->get()->each(fn (ProductImage $image) => $this->delete($image));
            }

            $attributes['sort_order'] ??= (int) ProductImage::query()->where('product_id', $product->id)->max('sort_order') + 1;

            return $this->createFor($product, $attributes);
        });
    }

    /**
     * Make one image the product's primary (first) image, keeping the order of the others.
     */
    public function makePrimary(Product $product, ProductImage $image): void
    {
        $ids = ProductImage::query()->where('product_id', $product->id)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();

        $this->reorder([$image->id, ...array_values(array_diff($ids, [$image->id]))]);
    }

    protected function model(): string
    {
        return ProductImage::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.product_image';
    }

    protected function relations(): array
    {
        return ['media'];
    }
}
