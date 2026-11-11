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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
     * Relations a storefront product card reads (ProductCardResource).
     *
     * @return array<int|string, mixed>
     */
    public static function cardRelations(): array
    {
        return [
            'gamingPlatform:id,name,slug',
            'primaryCategory:id,name,slug',
            'productImages' => static fn ($images) => $images->orderBy('sort_order'),
            'productImages.media',
        ];
    }

    /**
     * Storefront product listing. Runs a count, the page, and one query per eager load, whatever
     * the number of rows.
     */
    public function storefrontPage(ProductListingFilter $filter): LengthAwarePaginator
    {
        return $this->storefrontQuery($filter)->with(self::cardRelations())->paginate($filter->perPage, ['*'], 'page', $filter->page);
    }

    /**
     * The first products of a listing, without a count or eager loads (home carousels: the
     * caller loads card relations once for every carousel).
     *
     * @return Collection<int, Product>
     */
    public function storefrontTop(ProductListingFilter $filter, int $limit): Collection
    {
        return $this->storefrontQuery($filter)->limit($limit)->get();
    }

    /**
     * Listed products among these ids (same visibility as the listing), keyed by id, without
     * eager loads.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Product>
     */
    public function storefrontByIds(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        return $this->listed(ProductListingFilter::all())->whereIn('products.id', $ids)->get()->keyBy('id');
    }

    /**
     * Listed products among these ids with their lowest active base price (`price_from_base`)
     * and card relations, in the order of `$ids` (wishlist, M13).
     *
     * @param  list<int>  $ids
     * @return Collection<int, Product>
     */
    public function storefrontPricedByIds(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        $products = $this->storefrontQuery(ProductListingFilter::all())->whereIn('products.id', $ids)->with(self::cardRelations())->get()->keyBy('id');

        return new Collection(array_values(array_filter(array_map(static fn (int $id): ?Product => $products->get($id), $ids))));
    }

    /**
     * An active product by slug with its lowest active base price (`price_from_base`), whether
     * or not a variant is priced yet; null when unknown, not active or deleted.
     */
    public function storefrontDetail(string $slug): ?Product
    {
        return Product::query()
            ->select('products.*')
            ->selectSub($this->lowestActivePrice(), 'price_from_base')
            ->where('slug', $slug)
            ->where('status', ProductStatus::Active)
            ->with([
                ...self::cardRelations(),
                'regions:id,code,name',
                'productDeliveryFields.deliveryFieldDefinition',
                'productVariants' => static fn ($variants) => $variants->where('is_active', true)->with('region:id,code,name')->orderBy('sort_order'),
            ])
            ->first();
    }

    /**
     * Every listed product (same visibility as the listing), id, slug and update time only, for
     * the sitemap (M18).
     *
     * @return Builder<Product>
     */
    public function listedForSitemap(): Builder
    {
        return $this->listed(ProductListingFilter::all())->select(['products.id', 'products.slug', 'products.updated_at']);
    }

    /**
     * Listed products per category id (primary or secondary), as the category filter counts them.
     *
     * @param  list<int>  $categoryIds
     * @return array<int, int>
     */
    public function listedCountsByCategory(array $categoryIds): array
    {
        if ($categoryIds === []) {
            return [];
        }

        $primary = $this->listed(ProductListingFilter::all())->select(['products.id', 'products.primary_category_id as category_id'])->whereIn('primary_category_id', $categoryIds);
        $secondary = $this->listed(ProductListingFilter::all())
            ->select(['products.id', 'category_product.category_id'])
            ->join('category_product', 'category_product.product_id', '=', 'products.id')
            ->whereIn('category_product.category_id', $categoryIds);

        return DB::query()
            ->fromSub($primary->union($secondary), 'listed')
            ->selectRaw('category_id, COUNT(DISTINCT id) AS aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id')
            ->map(static fn (mixed $count): int => (int) $count)
            ->all();
    }

    /**
     * Listed products per gaming platform id.
     *
     * @param  list<int>  $platformIds
     * @return array<int, int>
     */
    public function listedCountsByPlatform(array $platformIds): array
    {
        if ($platformIds === []) {
            return [];
        }

        return $this->listed(ProductListingFilter::all())
            ->select('gaming_platform_id')
            ->selectRaw('COUNT(*) AS aggregate')
            ->whereIn('gaming_platform_id', $platformIds)
            ->groupBy('gaming_platform_id')
            ->pluck('aggregate', 'gaming_platform_id')
            ->map(static fn (mixed $count): int => (int) $count)
            ->all();
    }

    /**
     * @return Builder<Product>
     */
    private function storefrontQuery(ProductListingFilter $filter): Builder
    {
        $query = $this->listed($filter)
            ->select('products.*')
            ->selectSub($this->lowestActivePrice(), 'price_from_base');

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

        if ($filter->flag === 'featured') {
            $query->where('is_featured', true);
        } elseif ($filter->flag === 'trending') {
            $query->where('is_trending', true);
        }

        match ($filter->sort) {
            'newest' => $query->orderByDesc('published_at'),
            'price_asc' => $query->orderBy('price_from_base'),
            'price_desc' => $query->orderByDesc('price_from_base'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('sales_count'),
        };

        return $query->orderBy('id');
    }

    /**
     * What the storefront lists: active products with an active, priced variant matching the
     * variant-level filters (region and price).
     *
     * @return Builder<Product>
     */
    private function listed(ProductListingFilter $filter): Builder
    {
        return Product::query()
            ->where('status', ProductStatus::Active)
            ->whereExists($this->matchingVariants($filter));
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
