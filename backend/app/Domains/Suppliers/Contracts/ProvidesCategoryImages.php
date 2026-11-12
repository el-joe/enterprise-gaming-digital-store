<?php

namespace App\Domains\Suppliers\Contracts;

/**
 * Optional adapter capability (owner request 2026-11-12): the supplier's image for one of its
 * categories, fed to SupplierCategoryImages as a category-banner candidate. Return null when the
 * supplier has none. Reloadly categories carry only `id` and `name` (synced `raw.category` and
 * `GET /product-categories`, checked 2026-11-12), so its adapter returns null.
 */
interface ProvidesCategoryImages
{
    /**
     * @param  array<string, mixed>  $raw  one synced supplier product of that category (`raw`)
     */
    public function categoryImageUrl(string $categoryName, array $raw): ?string;
}
