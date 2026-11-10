<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Flags variants for pricing:recalculate-dirty. Each mark moves pricing_dirty_at forward by at
 * least a second, even within one wall-clock second, so a recalculation that read the previous
 * value can always tell that its data went out of date.
 */
class PricingDirtyMarker
{
    /**
     * @param  list<int>  $variantIds
     */
    public function forVariants(array $variantIds): void
    {
        if ($variantIds !== []) {
            $this->mark(ProductVariant::query()->whereIn('id', $variantIds));
        }
    }

    public function forProduct(int $productId): void
    {
        $this->mark(ProductVariant::query()->where('product_id', $productId));
    }

    public function forRule(PricingRule $rule): void
    {
        $query = ProductVariant::query();

        match ($rule->scope) {
            PricingRuleScope::Global => null,
            PricingRuleScope::Variant => $query->whereKey($rule->scope_id),
            PricingRuleScope::Product => $query->where('product_id', $rule->scope_id),
            PricingRuleScope::Platform => $query->whereIn('product_id', Product::query()->select('id')->where('gaming_platform_id', $rule->scope_id)),
            PricingRuleScope::Category => $query->whereIn('product_id', Product::query()->select('products.id')
                ->where('primary_category_id', $rule->scope_id)
                ->orWhereHas('categories', static fn (Builder $categories) => $categories->where('categories.id', $rule->scope_id))),
        };

        $this->mark($query);
    }

    public function all(): void
    {
        $this->mark(ProductVariant::query());
    }

    /**
     * A rate change moves the base cost of variants whose primary supplier cost is in that currency.
     *
     * @param  list<string>  $currencies
     */
    public function forCostCurrencies(array $currencies): void
    {
        if ($currencies === []) {
            return;
        }

        $this->mark(ProductVariant::query()->whereIn('id', SupplierProductMapping::query()
            ->select('product_variant_id')
            ->where('role', SupplierProductMappingRole::Primary)
            ->where('is_active', true)
            ->where(static function (Builder $query) use ($currencies): void {
                $query->whereIn('override_cost_currency', $currencies)->orWhereIn('supplier_unit_currency', $currencies);
            })));
    }

    /**
     * @param  Builder<ProductVariant>  $query
     */
    private function mark(Builder $query): void
    {
        $now = now()->toDateTimeString();

        $query->update(['pricing_dirty_at' => DB::raw(sprintf(
            "GREATEST(COALESCE(DATE_ADD(pricing_dirty_at, INTERVAL 1 SECOND), '%s'), '%s')",
            $now,
            $now,
        ))]);
    }
}
