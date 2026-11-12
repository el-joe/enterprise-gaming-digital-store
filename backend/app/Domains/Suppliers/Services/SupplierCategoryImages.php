<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Suppliers\Contracts\ProvidesCategoryImages;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Exceptions\SupplierNotConfigured;
use App\Domains\Suppliers\Jobs\FetchSupplierImageCandidate;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;

/**
 * Category images from the supplier (owner request 2026-11-12). For an adapter that implements
 * ProvidesCategoryImages, each distinct supplier category whose store category exists gets the
 * supplier's image queued as a category-banner candidate. No image is ever invented: an adapter
 * without the capability, or one that returns null (Reloadly), queues nothing.
 */
class SupplierCategoryImages
{
    public function __construct(private SupplierAdapterRegistry $registry, private SupplierCategoryResolver $categories) {}

    /**
     * @return int images queued
     */
    public function queue(Supplier $supplier, bool $force = false): int
    {
        try {
            $adapter = $this->registry->for($supplier);
        } catch (SupplierNotConfigured) {
            return 0;
        }

        if (! $adapter instanceof ProvidesCategoryImages) {
            return 0;
        }

        $queued = 0;
        $names = SupplierProduct::query()->where('supplier_id', $supplier->id)->whereNull('removed_at')
            ->whereNotNull('category_name')->where('category_name', '!=', '')
            ->distinct()->orderBy('category_name')->pluck('category_name');

        foreach ($names as $name) {
            $raw = SupplierProduct::query()->where('supplier_id', $supplier->id)->where('category_name', $name)->value('raw');
            $url = $adapter->categoryImageUrl((string) $name, is_array($raw) ? $raw : (array) json_decode((string) $raw, true));
            $category = $url === null ? null : $this->categories->match((string) $name);

            if ($category !== null) {
                FetchSupplierImageCandidate::dispatch($category->getMorphClass(), $category->id, $supplier->id, SupplierImageKind::CategoryBanner->value, $url, $force);
                $queued++;
            }
        }

        return $queued;
    }
}
