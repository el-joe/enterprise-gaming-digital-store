<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Pricing\DTOs\CostResult;
use App\Domains\Pricing\DTOs\PricingDataset;
use App\Domains\Shared\ValueObjects\Money;

/**
 * Step 1 of the precedence: the unit cost in the base currency. Costs use the rate without the
 * retail markup, and a stale cost rate makes the cost unavailable rather than quietly wrong.
 */
class CostResolver
{
    public function __construct(private ExchangeRateRegistry $rates, private CurrencyConverter $converter) {}

    public function resolve(ProductVariant $variant, PricingDataset $data): CostResult
    {
        $cost = $this->rawCost($variant, $data);

        if ($cost === null) {
            return new CostResult(null, 'cost_unavailable');
        }

        if ($cost->currency === $this->rates->baseCurrency()) {
            return new CostResult($cost, costCurrency: $cost->currency);
        }

        try {
            $rate = $this->rates->resolve($cost->currency);
        } catch (ExchangeRateMissing) {
            return new CostResult(null, 'cost_rate_missing', $cost->currency);
        }

        if ($rate->isStale) {
            return new CostResult(null, 'cost_rate_stale', $cost->currency);
        }

        return new CostResult($this->converter->toBase($cost, $rate, withoutMarkup: true), costCurrency: $cost->currency);
    }

    private function rawCost(ProductVariant $variant, PricingDataset $data): ?Money
    {
        $mapping = PricingDataLoader::primaryMapping($variant);

        if ($mapping !== null) {
            if ($mapping->override_cost !== null && $mapping->override_cost_currency !== null) {
                return Money::of($mapping->override_cost, $mapping->override_cost_currency);
            }

            $captured = $data->latestCosts[PricingDataLoader::costKey($mapping->supplier_product_id, $mapping->supplier_unit_price)] ?? null;

            if ($captured !== null) {
                return $captured;
            }
        }

        return $variant->manual_cost_base === null
            ? null
            : Money::of($variant->manual_cost_base, $this->rates->baseCurrency());
    }
}
