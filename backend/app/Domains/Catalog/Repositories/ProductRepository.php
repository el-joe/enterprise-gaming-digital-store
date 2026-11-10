<?php

namespace App\Domains\Catalog\Repositories;

use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository
{
    public function __construct(private ListQueryApplier $lists) {}

    /**
     * Admin product list.
     */
    public function adminPage(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            Product::query()->with(['gamingPlatform:id,name,slug', 'primaryCategory:id,name,slug'])->withCount('productVariants'),
            $list,
            ['status' => 'exact', 'delivery_type' => 'exact', 'gaming_platform_id' => 'exact', 'primary_category_id' => 'exact', 'is_featured' => 'bool', 'is_trending' => 'bool'],
            ['id', 'name', 'sku', 'sales_count', 'created_at', 'published_at'],
            ['name', 'slug', 'sku'],
        );
    }

    /**
     * Storefront product listing. Runs a count, the page, and one query per eager load, whatever
     * the number of rows.
     */
    public function storefrontPage(ProductListingFilter $filter): LengthAwarePaginator
    {
        $query = Product::query()
            ->select('products.*')
            ->selectSub($this->lowestActivePrice(), 'price_from_base')
            ->where('status', ProductStatus::Active)
            ->whereExists($this->matchingVariants($filter))
            ->with([
                'gamingPlatform:id,name,slug',
                'primaryCategory:id,name,slug',
                'productImages' => static fn ($images) => $images->orderBy('sort_order'),
                'productImages.media',
            ]);

        if ($filter->category !== null) {
            $categoryIds = Category::query()->select('id')->where('slug', $filter->category);

            $query->where(static function (Builder $inner) use ($categoryIds): void {
                $inner->whereIn('primary_category_id', $categoryIds)
                    ->orWhereHas('categories', static fn (Builder $categories) => $categories->whereIn('categories.id', $categoryIds));
            });
        }

        if ($filter->platform !== null) {
            $query->whereIn('gaming_platform_id', GamingPlatform::query()->select('id')->where('slug', $filter->platform));
        }

        if ($filter->deliveryType !== null) {
            $query->where('delivery_type', $filter->deliveryType);
        }

        match ($filter->sort) {
            'newest' => $query->orderByDesc('published_at'),
            'price_asc' => $query->orderBy('price_from_base'),
            'price_desc' => $query->orderByDesc('price_from_base'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('sales_count'),
        };

        return $query->orderBy('id')->paginate($filter->perPage, ['*'], 'page', $filter->page);
    }

    /**
     * @return Builder<ProductVariant>
     */
    private function lowestActivePrice(): Builder
    {
        return ProductVariant::query()
            ->selectRaw('MIN(price_base)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('is_active', true);
    }

    /**
     * @return Builder<ProductVariant>
     */
    private function matchingVariants(ProductListingFilter $filter): Builder
    {
        $variants = ProductVariant::query()
            ->selectRaw('1')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('is_active', true)
            ->whereNotNull('price_base');

        if ($filter->region !== null) {
            $variants->whereIn('region_id', Region::query()->select('id')->where('code', $filter->region));
        }

        if ($filter->minPriceBase !== null) {
            $variants->where('price_base', '>=', $filter->minPriceBase->amount);
        }

        if ($filter->maxPriceBase !== null) {
            $variants->where('price_base', '<=', $filter->maxPriceBase->amount);
        }

        return $variants;
    }
}
