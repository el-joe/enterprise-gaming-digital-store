<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Suppliers\DTOs\ProductQuery;
use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierSyncRunStatus;
use App\Domains\Suppliers\Enums\SupplierSyncRunType;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierSyncRun;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use Throwable;

/**
 * Catalog sync: pages through the supplier's products, upserts supplier_products (uq_spd), marks
 * products absent from a complete run as removed, and writes each fixed denomination's effective
 * cost to supplier_product_prices, which is the only cost source the pricing engine reads. One
 * run per supplier, type and window (uq_ssr_window).
 */
class SupplierCatalogSync
{
    private const int MAX_PAGES = 500;

    public function __construct(private SupplierAdapterRegistry $registry, private SupplierPriceWriter $prices) {}

    public function run(Supplier $supplier, string $triggeredBy, ?string $windowKey = null): ?SupplierSyncRun
    {
        $adapter = $this->registry->for($supplier);

        if (! $adapter->capabilities()->supports(SupplierCapability::ListProducts)) {
            return null;
        }

        $run = $this->open($supplier, SupplierSyncRunType::Catalog, $triggeredBy, $windowKey);

        if ($run === null) {
            return null;
        }

        $seen = [];
        $counts = ['items_seen' => 0, 'items_created' => 0, 'items_updated' => 0, 'items_failed' => 0];
        $startedAt = now();

        try {
            for ($page = 1, $isLast = false; ! $isLast && $page <= self::MAX_PAGES; $page++) {
                $result = $adapter->getProducts(new ProductQuery($page, (int) config('suppliers.reloadly.page_size')));
                $isLast = $result->isLast;

                foreach ($result->products as $product) {
                    $counts['items_seen']++;

                    try {
                        $created = $this->upsert($supplier, $product);
                        $counts[$created ? 'items_created' : 'items_updated']++;
                        $seen[] = $product->externalId;
                    } catch (Throwable $exception) {
                        report($exception);
                        $counts['items_failed']++;
                    }
                }
            }

            if ($isLast) {
                SupplierProduct::query()->where('supplier_id', $supplier->id)->whereNull('removed_at')
                    ->where(static fn ($query) => $query->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $startedAt))
                    ->update(['removed_at' => now()]);
            }

            $this->close($run, SupplierSyncRunStatus::Completed, $counts);
        } catch (SupplierRequestFailed $exception) {
            $this->close($run, SupplierSyncRunStatus::Failed, $counts, $exception->getMessage());
        }

        return $run->refresh();
    }

    public function open(Supplier $supplier, SupplierSyncRunType $type, string $triggeredBy, ?string $windowKey): ?SupplierSyncRun
    {
        try {
            $run = (new SupplierSyncRun)->forceFill([
                'supplier_id' => $supplier->id,
                'type' => $type,
                'triggered_by' => Str::limit($triggeredBy, 60),
                'status' => SupplierSyncRunStatus::Running,
                'started_at' => now(),
                'window_key' => $windowKey,
            ]);
            $run->save();

            return $run;
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }

    /**
     * @param  array<string, int>  $counts
     */
    public function close(SupplierSyncRun $run, SupplierSyncRunStatus $status, array $counts, ?string $error = null): void
    {
        $run->forceFill([...$counts, 'status' => $status, 'error' => $error, 'finished_at' => now()])->save();
    }

    /**
     * @return bool true when the row was created
     */
    public function upsert(Supplier $supplier, SupplierProductData $product): bool
    {
        $row = SupplierProduct::query()->firstOrNew(['supplier_id' => $supplier->id, 'external_id' => $product->externalId]);
        $created = ! $row->exists;

        $row->forceFill([
            'name' => $product->name,
            'brand_name' => $product->brandName,
            'country_code' => $product->countryCode,
            'category_name' => $product->categoryName,
            'denomination_type' => $product->denominationType,
            'currency' => $product->currency,
            'fixed_denominations' => $product->fixedDenominations,
            'min_denomination' => $product->minDenomination,
            'max_denomination' => $product->maxDenomination,
            'sender_currency' => $product->senderCurrency,
            'cost_map' => $product->costMap,
            'discount_pct' => $product->discountPct,
            'fee_fixed' => $product->feeFixed,
            'fee_pct' => $product->feePct,
            'supports_preorder' => $product->supportsPreorder,
            'requires_user_id' => $product->requiresUserId,
            'availability' => $product->availability,
            'raw' => $product->raw,
            'last_seen_at' => now(),
            'removed_at' => null,
        ])->save();

        $this->prices->writeFor($row);

        return $created;
    }
}
