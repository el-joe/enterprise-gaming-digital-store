<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
