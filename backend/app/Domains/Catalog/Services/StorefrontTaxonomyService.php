<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Active gaming platforms and categories for the storefront (M-A4), each with the number of
 * products the listing filter for it returns. Images are the platform's or category's own
 * uploads (BD8 governs what may be uploaded); nothing is added here.
 */
class StorefrontTaxonomyService
{
    public function __construct(private ProductRepository $products) {}

    /**
     * @return Collection<int, CatalogNode>
     */
    public function platforms(): Collection
    {
        $platforms = GamingPlatform::query()
            ->where('is_active', true)
            ->with(['logoMedia', 'bannerMedia'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $counts = $this->products->listedCountsByPlatform($platforms->modelKeys());

        return $platforms->toBase()->map(static fn (GamingPlatform $platform): CatalogNode => new CatalogNode($platform, $counts[$platform->id] ?? 0));
    }

    /**
     * Active categories whose parent, if any, is active too; parents come first.
     *
     * @return Collection<int, CatalogNode>
     */
    public function categories(): Collection
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->where(static fn (Builder $query) => $query->whereNull('parent_id')->orWhereHas('parent', static fn (Builder $parent) => $parent->where('is_active', true)))
            ->with(['bannerMedia', 'parent:id,slug'])
            ->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $counts = $this->products->listedCountsByCategory($categories->modelKeys());

        return $categories->toBase()->map(static fn (Category $category): CatalogNode => new CatalogNode($category, $counts[$category->id] ?? 0));
    }
}
