<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierSyncRunStatus;
use App\Domains\Suppliers\Enums\SupplierSyncRunType;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierSyncRun;

/**
 * Pricing sync for mapped supplier products only: re-reads each product's pricing and writes
 * effective costs through SupplierPriceWriter.
 */
class SupplierPriceSync
{
    public function __construct(private SupplierAdapterRegistry $registry, private SupplierCatalogSync $catalog) {}

    public function run(Supplier $supplier, string $triggeredBy, ?string $windowKey = null): ?SupplierSyncRun
    {
        $adapter = $this->registry->for($supplier);

        if (! $adapter->capabilities()->supports(SupplierCapability::Pricing)) {
            return null;
        }

        $run = $this->catalog->open($supplier, SupplierSyncRunType::Pricing, $triggeredBy, $windowKey);

        if ($run === null) {
            return null;
        }

        $counts = ['items_seen' => 0, 'items_created' => 0, 'items_updated' => 0, 'items_failed' => 0];
        $products = SupplierProduct::query()->where('supplier_id', $supplier->id)
            ->whereIn('id', SupplierProductMapping::query()->select('supplier_product_id')->where('supplier_id', $supplier->id))
            ->orderBy('id')->get();

        foreach ($products as $product) {
            $counts['items_seen']++;

            try {
                $this->catalog->upsert($supplier, $adapter->getPricing($product->external_id)->product);
                $counts['items_updated']++;
            } catch (SupplierRequestFailed) {
                $counts['items_failed']++;
            }
        }

        $this->catalog->close($run, $counts['items_failed'] > 0 && $counts['items_updated'] === 0 && $counts['items_seen'] > 0 ? SupplierSyncRunStatus::Failed : SupplierSyncRunStatus::Completed, $counts);

        return $run->refresh();
    }
}
