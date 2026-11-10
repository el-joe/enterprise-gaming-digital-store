<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;

/**
 * Appends effective costs to supplier_product_prices (inherited from M6: the pricing engine reads
 * cost only from there). Fixed denominations come from the synced cost map; range products get a
 * row for each mapped denomination. A row is written when the cost changed, or when the latest is
 * older than half of `max_price_age_minutes`, so freshness survives an unchanged price. Variants
 * mapped to a changed product are marked for listing-price recalculation.
 */
class SupplierPriceWriter
{
    public function __construct(private SupplierCostCalculator $costs, private PricingDirtyMarker $dirty) {}

    /**
     * @return int rows written
     */
    public function writeFor(SupplierProduct $product): int
    {
        $denominations = array_keys(array_filter(is_array($product->cost_map) ? $product->cost_map : [], static fn (mixed $value, int|string $key): bool => $key !== 'rate', ARRAY_FILTER_USE_BOTH));
        $mapped = SupplierProductMapping::query()->where('supplier_product_id', $product->id)->pluck('supplier_unit_price')->all();
        $denominations = array_unique([...array_map('strval', $denominations), ...array_map('strval', $mapped)]);

        $written = 0;
        $refreshBefore = now()->subMinutes(intdiv((int) config('suppliers.max_price_age_minutes'), 2));

        foreach ($denominations as $denomination) {
            $price = $this->costs->forStoredProduct($product, $denomination);

            if ($price === null) {
                continue;
            }

            $latest = SupplierProductPrice::query()->where('supplier_product_id', $product->id)
                ->where('denomination', $price->denomination)->latest('id')->first();

            $unchanged = $latest !== null && bccomp($latest->cost, $price->cost, 6) === 0 && $latest->cost_currency === $price->costCurrency;

            if ($unchanged && $latest->captured_at->gt($refreshBefore)) {
                continue;
            }

            (new SupplierProductPrice)->forceFill([
                'supplier_product_id' => $product->id,
                'denomination' => $price->denomination,
                'cost' => $price->cost,
                'cost_currency' => $price->costCurrency,
                'captured_at' => now(),
            ])->save();
            $written++;

            if (! $unchanged) {
                $this->dirty->forVariants(SupplierProductMapping::query()->where('supplier_product_id', $product->id)->pluck('product_variant_id')->all());
            }
        }

        return $written;
    }
}
