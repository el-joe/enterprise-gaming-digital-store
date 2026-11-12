<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\CategoryService;
use App\Domains\Catalog\Services\ReservedSlugs;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Store categories from the supplier's own category names (`supplier_products.category_name`,
 * owner decision). An existing category with the same name (case-insensitive) is reused;
 * otherwise one is created through CategoryService (audited `catalog.category.created`): active,
 * English name, a free and non-reserved slug, Arabic left for staff. Two concurrent creators
 * cannot both insert: the unique slug refuses the second, which then reads the winner's row.
 */
class SupplierCategoryResolver
{
    public function __construct(private CategoryService $categories, private ReservedSlugs $reserved) {}

    public function resolve(string $name): ?Category
    {
        $name = trim(Str::limit(trim($name), 120, ''));

        if ($name === '') {
            return null;
        }

        $existing = $this->find($name);

        if ($existing !== null) {
            return $existing;
        }

        $slug = $this->freeSlug($name);

        try {
            return $this->categories->create(['name' => $name, 'slug' => $slug, 'is_active' => true]);
        } catch (ApplicationException) {
            return $this->find($name, lock: true) ?? Category::query()->where('slug', $slug)->lockForUpdate()->firstOrFail();
        }
    }

    /**
     * Create the missing store categories for every distinct supplier category name.
     *
     * @return array{created: int, existing: int}
     */
    public function createAll(?int $supplierId = null): array
    {
        $names = SupplierProduct::query()->whereNotNull('category_name')->where('category_name', '!=', '')
            ->when($supplierId !== null, static fn ($query) => $query->where('supplier_id', $supplierId))
            ->distinct()->orderBy('category_name')->pluck('category_name');

        $counts = ['created' => 0, 'existing' => 0];

        foreach ($names as $name) {
            DB::transaction(function () use ($name, &$counts): void {
                $category = $this->resolve((string) $name);
                $category?->wasRecentlyCreated ? $counts['created']++ : $counts['existing']++;
            });
        }

        return $counts;
    }

    /**
     * The existing store category for a supplier category name, never created.
     */
    public function match(string $name): ?Category
    {
        $name = trim(Str::limit(trim($name), 120, ''));

        return $name === '' ? null : $this->find($name);
    }

    private function find(string $name, bool $lock = false): ?Category
    {
        return Category::query()->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->when($lock, static fn ($query) => $query->lockForUpdate())
            ->orderBy('id')->first();
    }

    private function freeSlug(string $name): string
    {
        $base = Str::limit(Str::slug($name), 120, '') ?: 'category';
        $base = $this->reserved->isReserved($base) ? $base.'-cards' : $base;

        for ($suffix = 1; $suffix < 50; $suffix++) {
            $slug = $suffix === 1 ? $base : $base.'-'.$suffix;

            if (! Category::withTrashed()->where('slug', $slug)->exists()) {
                return $slug;
            }
        }

        return $base.'-'.Str::lower(Str::random(6));
    }
}
