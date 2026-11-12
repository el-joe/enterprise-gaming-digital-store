<?php

namespace App\Domains\Catalog\Repositories;

use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
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
            'gamingPlatform:id,name,slug,logo_media_id',
            'gamingPlatform.logoMedia',
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

        return $this->withCardAggregates($this->listed(ProductListingFilter::all())->select('products.*')->selectSub($this->lowestActivePrice(), 'price_from_base'))
            ->whereIn('products.id', $ids)->get()->keyBy('id');
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
        return $this->withCardAggregates(Product::query()
            ->select('products.*')
            ->selectSub($this->lowestActivePrice(), 'price_from_base'))
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
     * Listed products per category id and value of a product column (`gaming_platform_id` or
     * `delivery_type`), primary or secondary category: the mega menu's top brands per category.
     *
     * @param  list<int>  $categoryIds
     * @return list<array{category_id: int, value: int|string, count: int}>
     */
    public function listedCountsByCategoryAnd(array $categoryIds, string $column): array
    {
        if ($categoryIds === [] || ! in_array($column, ['gaming_platform_id', 'delivery_type'], true)) {
            return [];
        }

        $primary = $this->listed(ProductListingFilter::all())
            ->select(['products.id', 'products.primary_category_id as category_id', "products.{$column} as facet_value"])
            ->whereIn('primary_category_id', $categoryIds);
        $secondary = $this->listed(ProductListingFilter::all())
            ->select(['products.id', 'category_product.category_id', "products.{$column} as facet_value"])
            ->join('category_product', 'category_product.product_id', '=', 'products.id')
            ->whereIn('category_product.category_id', $categoryIds);

        return DB::query()
            ->fromSub($primary->union($secondary), 'listed')
            ->whereNotNull('facet_value')
            ->selectRaw('category_id, facet_value, COUNT(DISTINCT id) AS aggregate')
            ->groupBy('category_id', 'facet_value')
            ->get()
            ->map(static fn (object $row): array => ['category_id' => (int) $row->category_id, 'value' => $row->facet_value, 'count' => (int) $row->aggregate])
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
     * Listed products per category id for a filter without its category facet (primary or
     * secondary category), as the category filter counts them (Storefront v2 facets).
     *
     * @return array<int, int>
     */
    public function facetCountsByCategory(ProductListingFilter $filter): array
    {
        $filter = $filter->without('category');
        $primary = $this->filtered($filter)->select(['products.id', 'products.primary_category_id as category_id'])->whereNotNull('primary_category_id');
        $secondary = $this->filtered($filter)
            ->select(['products.id', 'category_product.category_id'])
            ->join('category_product', 'category_product.product_id', '=', 'products.id');

        return $this->intCounts(DB::query()
            ->fromSub($primary->union($secondary), 'listed')
            ->selectRaw('category_id, COUNT(DISTINCT id) AS aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id'));
    }

    /**
     * Listed products per gaming platform (brand) id for a filter without its brand facet.
     *
     * @return array<int, int>
     */
    public function facetCountsByPlatform(ProductListingFilter $filter): array
    {
        return $this->intCounts($this->filtered($filter->without('platform'))
            ->toBase()
            ->select('gaming_platform_id')
            ->selectRaw('COUNT(*) AS aggregate')
            ->whereNotNull('gaming_platform_id')
            ->groupBy('gaming_platform_id')
            ->pluck('aggregate', 'gaming_platform_id'));
    }

    /**
     * Listed products per delivery type for a filter without its delivery-type facet.
     *
     * @return array<string, int>
     */
    public function facetCountsByDeliveryType(ProductListingFilter $filter): array
    {
        return $this->intCounts($this->filtered($filter->without('delivery_type'))
            ->toBase()
            ->select('delivery_type')
            ->selectRaw('COUNT(*) AS aggregate')
            ->groupBy('delivery_type')
            ->pluck('aggregate', 'delivery_type'));
    }

    /**
     * Listed products per variant region id for a filter without its region facet: a product
     * counts for a region when one of its variants there matches the other variant filters.
     *
     * @return array<int, int>
     */
    public function facetCountsByRegion(ProductListingFilter $filter): array
    {
        $filter = $filter->without('region');
        $variants = $this->variantConditions(ProductVariant::query()->select(['product_id', 'region_id'])->whereNotNull('region_id'), $filter);

        return $this->intCounts($this->productFilters(Product::query()->where('status', ProductStatus::Active), $filter)
            ->toBase()
            ->joinSub($variants->toBase(), 'facet_variants', 'facet_variants.product_id', '=', 'products.id')
            ->selectRaw('facet_variants.region_id AS region_id, COUNT(DISTINCT products.id) AS aggregate')
            ->groupBy('facet_variants.region_id')
            ->pluck('aggregate', 'region_id'));
    }

    /**
     * Number of listed products matching a filter (the listing total without a page).
     */
    public function storefrontCount(ProductListingFilter $filter): int
    {
        return $this->filtered($filter)->count();
    }

    /**
     * Listed products for a filter with "in stock only" switched on.
     */
    public function facetInStockCount(ProductListingFilter $filter): int
    {
        $current = $filter->without('in_stock');

        return $this->filtered(new ProductListingFilter(
            categories: $current->categorySlugs(),
            platforms: $current->platformSlugs(),
            regions: $current->regionCodes(),
            deliveryTypes: $current->deliveryTypeValues(),
            minPriceBase: $current->minPriceBase,
            maxPriceBase: $current->maxPriceBase,
            flag: $current->flag,
            inStock: true,
            onSale: $current->onSale,
        ))->count();
    }

    /**
     * Lowest and highest base price of a matching variant for a filter without its price range.
     *
     * @return array{min: ?string, max: ?string}
     */
    public function facetPriceBounds(ProductListingFilter $filter): array
    {
        $filter = $filter->without('price');
        $variants = $this->variantConditions(ProductVariant::query()->select(['product_id', 'price_base']), $filter);

        $row = $this->productFilters(Product::query()->where('status', ProductStatus::Active), $filter)
            ->toBase()
            ->joinSub($variants->toBase(), 'facet_variants', 'facet_variants.product_id', '=', 'products.id')
            ->selectRaw('MIN(facet_variants.price_base) AS min_price, MAX(facet_variants.price_base) AS max_price')
            ->first();

        return [
            'min' => $row?->min_price === null ? null : (string) $row->min_price,
            'max' => $row?->max_price === null ? null : (string) $row->max_price,
        ];
    }

    /**
     * @param  BaseCollection<int|string, mixed>  $counts
     * @return array<int|string, int>
     */
    private function intCounts(BaseCollection $counts): array
    {
        return $counts->map(static fn (mixed $count): int => (int) $count)->all();
    }

    /**
     * @return Builder<Product>
     */
    private function storefrontQuery(ProductListingFilter $filter): Builder
    {
        $query = $this->withCardAggregates($this->filtered($filter)
            ->select('products.*')
            ->selectSub($this->lowestActivePrice(), 'price_from_base'));

        if ($filter->salesPeriodDays !== null && in_array($filter->sort, ['popular', 'best_selling'], true)) {
            $query->orderByDesc(OrderItem::query()
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereColumn('order_items.product_id', 'products.id')
                ->where('orders.paid_at', '>=', now()->subDays($filter->salesPeriodDays)));
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
     * Listed products matching every filter of the listing (no select, no order).
     *
     * @return Builder<Product>
     */
    private function filtered(ProductListingFilter $filter): Builder
    {
        return $this->productFilters($this->listed($filter), $filter);
    }

    /**
     * The product-level filters: categories (primary or secondary), brands, delivery types and
     * the home flags. Values inside one facet are alternatives.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function productFilters(Builder $query, ProductListingFilter $filter): Builder
    {
        if (($categories = $filter->categorySlugs()) !== []) {
            $categoryIds = Category::query()->select('id')->whereIn('slug', $categories);

            $query->where(static function (Builder $inner) use ($categoryIds): void {
                $inner->whereIn('primary_category_id', $categoryIds)
                    ->orWhereHas('categories', static fn (Builder $categories) => $categories->whereIn('categories.id', $categoryIds));
            });
        }

        if (($platforms = $filter->platformSlugs()) !== []) {
            $query->whereIn('gaming_platform_id', GamingPlatform::query()->select('id')->whereIn('slug', $platforms));
        }

        if (($deliveryTypes = $filter->deliveryTypeValues()) !== []) {
            $query->whereIn('delivery_type', $deliveryTypes);
        }

        if ($filter->flag === 'featured') {
            $query->where('is_featured', true);
        } elseif ($filter->flag === 'trending') {
            $query->where('is_trending', true);
        }

        return $query;
    }

    /**
     * What the storefront lists: active products with an active, priced variant matching the
     * variant-level filters (region, price, stock and sale, all on the same variant).
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
     * Card facts from the sellable variants, as subqueries of the same select (no extra query):
     * the face value (`face_min`, `face_max`, `face_currency`, `face_currencies` over fixed
     * amounts; `custom_min`, `custom_max` over custom amounts), the purchasable variant count
     * and, for one, its public id (`quick_variant`), and `required_fields`, the delivery fields
     * the customer must fill (Storefront v2 card: face value and quick add).
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function withCardAggregates(Builder $query): Builder
    {
        $sellable = static fn (string $expression): Builder => ProductVariant::query()
            ->selectRaw($expression)
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.is_active', true)
            ->whereNotNull('product_variants.price_base');
        $fixed = static fn (string $expression): Builder => $sellable($expression)->where('product_variants.is_custom_amount', false)->whereNotNull('product_variants.denomination_value');
        $purchasable = static fn (string $expression): Builder => $sellable($expression)->where('product_variants.is_custom_amount', false)->where('product_variants.availability', '!=', ProductVariantAvailability::OutOfStock);

        return $query
            ->selectSub($fixed('MIN(product_variants.denomination_value)'), 'face_min')
            ->selectSub($fixed('MAX(product_variants.denomination_value)'), 'face_max')
            ->selectSub($fixed('MIN(product_variants.denomination_currency)'), 'face_currency')
            ->selectSub($fixed('COUNT(DISTINCT product_variants.denomination_currency)'), 'face_currencies')
            ->selectSub($sellable('MIN(product_variants.custom_min)')->where('product_variants.is_custom_amount', true), 'custom_min')
            ->selectSub($sellable('MAX(product_variants.custom_max)')->where('product_variants.is_custom_amount', true), 'custom_max')
            ->selectSub($sellable('MIN(product_variants.denomination_currency)')->where('product_variants.is_custom_amount', true), 'custom_currency')
            ->selectSub($purchasable('COUNT(*)'), 'purchasable_variants')
            ->selectSub($purchasable('MIN(product_variants.public_id)'), 'quick_variant')
            ->selectSub(ProductDeliveryField::query()->selectRaw('COUNT(*)')->whereColumn('product_delivery_fields.product_id', 'products.id')->where('is_required', true), 'required_fields');
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
        return $this->variantConditions(
            ProductVariant::query()->selectRaw('1')->whereColumn('product_variants.product_id', 'products.id'),
            $filter,
        );
    }

    /**
     * A sellable variant (active and priced) matching the variant-level filters.
     *
     * @param  Builder<ProductVariant>  $variants
     * @return Builder<ProductVariant>
     */
    private function variantConditions(Builder $variants, ProductListingFilter $filter): Builder
    {
        $variants->where('product_variants.is_active', true)->whereNotNull('product_variants.price_base');

        if (($regions = $filter->regionCodes()) !== []) {
            $variants->whereIn('product_variants.region_id', Region::query()->select('id')->whereIn('code', $regions));
        }

        if ($filter->minPriceBase !== null) {
            $variants->where('product_variants.price_base', '>=', $filter->minPriceBase->amount);
        }

        if ($filter->maxPriceBase !== null) {
            $variants->where('product_variants.price_base', '<=', $filter->maxPriceBase->amount);
        }

        if ($filter->inStock) {
            $variants->whereIn('product_variants.availability', [ProductVariantAvailability::InStock, ProductVariantAvailability::Limited]);
        }

        if ($filter->onSale) {
            $variants->whereNotNull('product_variants.compare_at_base')->whereColumn('product_variants.compare_at_base', '>', 'product_variants.price_base');
        }

        return $variants;
    }
}
