<?php

namespace App\Domains\Mcp\Repositories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Read-only catalog queries behind the MCP products tools. Fixed columns and fixed filters only.
 */
class McpCatalogRepository
{
    private const array PRODUCT_RELATIONS = ['gamingPlatform:id,name,slug', 'primaryCategory:id,name,slug'];

    /**
     * @param  array{status?: ?string, platform?: ?string, category?: ?string}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function productsPage(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->with(self::PRODUCT_RELATIONS)
            ->withCount(['productVariants as active_variants_count' => static fn (Builder $query) => $query->where('is_active', true)])
            ->when($filters['status'] ?? null, static fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['platform'] ?? null, static fn (Builder $query, string $slug) => $query->whereHas('gamingPlatform', static fn (Builder $platform) => $platform->where('slug', $slug)))
            ->when($filters['category'] ?? null, static fn (Builder $query, string $slug) => $query->whereHas('primaryCategory', static fn (Builder $category) => $category->where('slug', $slug)))
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function searchProducts(string $term, int $page, int $perPage): LengthAwarePaginator
    {
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        return Product::query()
            ->with(self::PRODUCT_RELATIONS)
            ->withCount(['productVariants as active_variants_count' => static fn (Builder $query) => $query->where('is_active', true)])
            ->where(static fn (Builder $query) => $query
                ->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhereHas('productVariants', static fn (Builder $variants) => $variants->where('sku', 'like', $like)))
            ->orderByDesc('sales_count')
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    public function findProduct(string $publicIdOrSku): ?Product
    {
        return Product::query()
            ->with([...self::PRODUCT_RELATIONS, 'productVariants' => static fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->where(static fn (Builder $query) => Str::isUlid($publicIdOrSku)
                ? $query->where('public_id', $publicIdOrSku)->orWhere('sku', $publicIdOrSku)
                : $query->where('sku', $publicIdOrSku))
            ->first();
    }

    public function findProductBySku(string $sku): ?Product
    {
        return Product::query()->where('sku', $sku)->first();
    }

    public function findVariantBySku(string $sku): ?ProductVariant
    {
        return ProductVariant::query()->with('product')->where('sku', $sku)->first();
    }

    /**
     * Active supplier mappings for a variant with supplier status and availability; never
     * credentials or supplier payloads.
     *
     * @return Collection<int, SupplierProductMapping>
     */
    public function mappingsFor(ProductVariant $variant): Collection
    {
        return SupplierProductMapping::query()
            ->with('supplier:id,name,status,health')
            ->where('product_variant_id', $variant->id)
            ->orderBy('priority')
            ->get(['id', 'product_variant_id', 'supplier_id', 'role', 'priority', 'is_active', 'availability', 'last_verified_at']);
    }

    /**
     * Active, priced variants of active products, for the margin scan.
     *
     * @return Collection<int, ProductVariant>
     */
    public function activePricedVariants(int $limit): Collection
    {
        return ProductVariant::query()
            ->with('product')
            ->where('is_active', true)
            ->where('is_custom_amount', false)
            ->whereNotNull('price_base')
            ->whereHas('product', static fn (Builder $query) => $query->where('status', 'active'))
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
