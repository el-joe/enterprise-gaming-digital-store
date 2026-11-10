<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Payments\Models\PaymentGatewayFeeRule;
use App\Domains\Pricing\DTOs\PricingDataset;
use App\Domains\Pricing\Repositories\PricingRuleRepository;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use Illuminate\Support\Collection;

class PricingDataLoader
{
    public function __construct(private PricingRuleRepository $rules) {}

    /**
     * Load relations the engine reads and the shared data for this batch of variants.
     *
     * @param  Collection<int, ProductVariant>  $variants
     */
    public function load(Collection $variants): PricingDataset
    {
        $variants->loadMissing([
            'product:id,gaming_platform_id,primary_category_id',
            'product.categories:id',
            'supplierProductMappings' => static fn ($query) => $query
                ->where('role', SupplierProductMappingRole::Primary)
                ->where('is_active', true),
            'variantPrices',
        ]);

        return new PricingDataset(
            $this->rules->activeNow(),
            $this->activePromotions(),
            PaymentGatewayFeeRule::query()->where('is_active', true)->get(),
            $this->latestCosts($variants),
        );
    }

    /**
     * @return Collection<int, Promotion>
     */
    private function activePromotions(): Collection
    {
        return Promotion::query()
            ->with('promotionTargets')
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(static fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->get();
    }

    /**
     * Latest captured supplier cost per primary mapping and denomination.
     *
     * @param  Collection<int, ProductVariant>  $variants
     * @return array<string, Money>
     */
    private function latestCosts(Collection $variants): array
    {
        $mappings = $variants->flatMap(static fn (ProductVariant $variant): Collection => $variant->supplierProductMappings);

        if ($mappings->isEmpty()) {
            return [];
        }

        $latest = SupplierProductPrice::query()
            ->selectRaw('MAX(id) AS id')
            ->whereIn('supplier_product_id', $mappings->pluck('supplier_product_id')->unique())
            ->groupBy('supplier_product_id', 'denomination');

        return SupplierProductPrice::query()
            ->whereIn('id', $latest->pluck('id'))
            ->get()
            ->mapWithKeys(static fn (SupplierProductPrice $price): array => [
                self::costKey($price->supplier_product_id, $price->denomination) => Money::of($price->cost, $price->cost_currency),
            ])
            ->all();
    }

    public static function costKey(int $supplierProductId, string $denomination): string
    {
        return $supplierProductId.'|'.$denomination;
    }

    /**
     * @return SupplierProductMapping|null the variant's primary mapping, if any
     */
    public static function primaryMapping(ProductVariant $variant): ?SupplierProductMapping
    {
        return $variant->supplierProductMappings->first(
            static fn (SupplierProductMapping $mapping): bool => $mapping->role === SupplierProductMappingRole::Primary && $mapping->is_active,
        );
    }
}
