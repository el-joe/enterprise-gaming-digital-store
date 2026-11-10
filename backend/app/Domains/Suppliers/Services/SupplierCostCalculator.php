<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Suppliers\DTOs\SupplierPriceData;
use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Models\SupplierProduct;

/**
 * The effective cost of one denomination in the supplier's sender currency, from the synced
 * catalog: sender amount (fixed map, or denomination x rate for range products) plus the fixed
 * and percentage sender fees, minus the discount only when `apply_discount_to_cost` is enabled.
 *
 * How fees and discounts combine is UNVERIFIED (INTEGRATIONS.md 1.2 names the fields, not the
 * formula), so both fees are added and the discount is ignored by default: the estimate errs high.
 * The actual cost of every purchase is recorded from the supplier's own response, and the daily
 * reconciliation reports any difference.
 */
class SupplierCostCalculator
{
    private const int SCALE = 6;

    /**
     * @return list<SupplierPriceData>
     */
    public function forFixedDenominations(SupplierProductData $product): array
    {
        if ($product->denominationType !== DenominationType::Fixed || $product->senderCurrency === null) {
            return [];
        }

        $prices = [];

        foreach ($product->costMap as $denomination => $sender) {
            $prices[] = new SupplierPriceData((string) $denomination, $this->effective($sender, $product->feeFixed, $product->feePct, $product->discountPct), $product->senderCurrency);
        }

        return $prices;
    }

    /**
     * Cost of a denomination of a stored supplier product, or null when it cannot be derived.
     */
    public function forStoredProduct(SupplierProduct $product, string $denomination): ?SupplierPriceData
    {
        $denomination = ExactJson::decimal(bcadd($denomination, '0', self::SCALE)) ?? $denomination;

        if ($product->sender_currency === null) {
            return null;
        }

        $map = is_array($product->cost_map) ? $product->cost_map : [];

        $sender = match ($product->denomination_type) {
            DenominationType::Fixed => $map[$denomination] ?? null,
            DenominationType::Range => isset($map['rate']) && $this->withinRange($product, $denomination)
                ? bcmul($denomination, (string) $map['rate'], self::SCALE)
                : null,
            default => null,
        };

        if (! is_string($sender) || ExactJson::decimal($sender) === null) {
            return null;
        }

        return new SupplierPriceData($denomination, $this->effective($sender, $product->fee_fixed, $product->fee_pct, $product->discount_pct), $product->sender_currency);
    }

    private function effective(string $sender, ?string $feeFixed, ?string $feePct, ?string $discountPct): string
    {
        $cost = bcadd($sender, $feeFixed ?? '0', self::SCALE);
        $cost = bcadd($cost, bcdiv(bcmul($sender, $feePct ?? '0', self::SCALE + 4), '100', self::SCALE), self::SCALE);

        if (config('suppliers.apply_discount_to_cost') === true && $discountPct !== null) {
            $cost = bcsub($cost, bcdiv(bcmul($sender, $discountPct, self::SCALE + 4), '100', self::SCALE), self::SCALE);
        }

        return $cost;
    }

    private function withinRange(SupplierProduct $product, string $denomination): bool
    {
        return ($product->min_denomination === null || bccomp($denomination, $product->min_denomination, self::SCALE) >= 0)
            && ($product->max_denomination === null || bccomp($denomination, $product->max_denomination, self::SCALE) <= 0);
    }
}
