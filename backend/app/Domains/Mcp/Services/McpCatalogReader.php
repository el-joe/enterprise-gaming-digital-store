<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Repositories\McpCatalogRepository;
use App\Domains\Mcp\Repositories\McpOperationsRepository;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Pricing, availability and margin reads for the MCP products and inventory tools. Prices come
 * from the same PricingEngine the storefront uses, so MCP never shows a price the store would not
 * charge.
 */
class McpCatalogReader
{
    public function __construct(
        private McpCatalogRepository $catalog,
        private McpOperationsRepository $operations,
        private PricingEngine $engine,
        private ExchangeRateRegistry $rates,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function pricing(string $variantSku, ?string $country, ?string $currency): array
    {
        $variant = $this->variant($variantSku);
        $base = $this->rates->baseCurrency();
        $currency = $currency === null ? $base : strtoupper($currency);

        $quote = $this->engine->quote($variant, new PricingContext(country: $country === null ? null : strtoupper($country), currency: $currency));

        return [
            'variant_sku' => $variant->sku,
            'product_sku' => $variant->product->sku,
            'country' => $country === null ? null : strtoupper($country),
            'purchasable' => $quote->purchasable,
            'block_reason' => $quote->blockReason,
            'price' => McpFormat::money($quote->amount?->amount, $quote->amount?->currency, $this->exponent($currency)),
            'price_base' => McpFormat::money($quote->baseAmount?->amount, $base),
            'source' => $quote->source?->value,
            'cost_base' => McpFormat::money($quote->cost?->amount, $quote->cost?->currency),
            'margin_floor_base' => McpFormat::money($quote->floor?->amount, $quote->floor?->currency),
            'margin_pct' => $quote->baseAmount !== null && $quote->cost !== null ? McpFormat::percent(bcsub($quote->baseAmount->amount, $quote->cost->amount, 6), $quote->baseAmount->amount) : null,
            'raised_to_floor' => $quote->raisedToFloor,
            'rate' => $quote->rate === null ? null : ['value' => $quote->rate->rate->value, 'source' => $quote->rate->source, 'is_stale' => $quote->rate->isStale],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function availability(string $variantSku): array
    {
        $variant = $this->variant($variantSku);
        $mappings = $this->catalog->mappingsFor($variant);

        return [
            'variant_sku' => $variant->sku,
            'product_sku' => $variant->product->sku,
            'product_status' => $variant->product->status->value,
            'variant_active' => $variant->is_active,
            'availability' => $variant->availability?->value,
            'sellable' => $variant->is_active && $variant->product->status === ProductStatus::Active && $variant->availability?->value !== 'out_of_stock',
            'internal_stock_available' => $this->operations->availableStockCount($variant),
            'suppliers' => $mappings->map(static fn (SupplierProductMapping $mapping): array => [
                'supplier' => $mapping->supplier?->name,
                'supplier_status' => $mapping->supplier?->status?->value,
                'supplier_health' => $mapping->supplier?->health?->value,
                'role' => $mapping->role?->value,
                'priority' => $mapping->priority,
                'mapping_active' => $mapping->is_active,
                'availability' => $mapping->availability?->value,
                'last_verified_at' => McpFormat::iso($mapping->last_verified_at),
            ])->values()->all(),
        ];
    }

    /**
     * Active variants whose margin over cost is below the threshold, lowest first. Variants with no
     * known cost are counted separately: their margin cannot be computed.
     *
     * @return array{page: LengthAwarePaginator<int, array<string, mixed>>, scanned: int, cost_unknown: int, threshold_pct: string, truncated: bool}
     */
    public function lowMargin(string $thresholdPct, int $page, int $perPage): array
    {
        $limit = (int) config('mcp.low_margin_scan_limit');
        $variants = $this->catalog->activePricedVariants($limit + 1);
        $truncated = $variants->count() > $limit;
        $variants = $variants->take($limit);
        $base = $this->rates->baseCurrency();

        $quotes = $variants->isEmpty() ? collect() : $this->engine->quoteMany($variants, new PricingContext);
        $costUnknown = 0;
        $rows = [];

        foreach ($variants as $variant) {
            $quote = $quotes->get($variant->id);

            if ($quote?->baseAmount === null || $quote->cost === null || ! $quote->baseAmount->isPositive()) {
                $costUnknown++;

                continue;
            }

            $margin = McpFormat::percent(bcsub($quote->baseAmount->amount, $quote->cost->amount, 6), $quote->baseAmount->amount);

            if ($margin !== null && bccomp($margin, $thresholdPct, 2) === -1) {
                $rows[] = [
                    'variant_sku' => $variant->sku,
                    'variant_title' => $variant->title,
                    'product_sku' => $variant->product->sku,
                    'product_name' => $variant->product->name,
                    'price_base' => McpFormat::money($quote->baseAmount->amount, $base),
                    'cost_base' => McpFormat::money($quote->cost->amount, $quote->cost->currency),
                    'margin_floor_base' => McpFormat::money($quote->floor?->amount, $quote->floor?->currency),
                    'margin_pct' => $margin,
                    'raised_to_floor' => $quote->raisedToFloor,
                ];
            }
        }

        usort($rows, static fn (array $a, array $b): int => bccomp($a['margin_pct'], $b['margin_pct'], 2) ?: strcmp($a['variant_sku'], $b['variant_sku']));

        return [
            'page' => new LengthAwarePaginator(array_slice($rows, ($page - 1) * $perPage, $perPage), count($rows), $perPage, $page),
            'scanned' => $variants->count(),
            'cost_unknown' => $costUnknown,
            'threshold_pct' => $thresholdPct,
            'truncated' => $truncated,
        ];
    }

    private function variant(string $sku): ProductVariant
    {
        return $this->catalog->findVariantBySku($sku)
            ?? throw new ApplicationException('No variant has this SKU.', 'not_found', 404);
    }

    private function exponent(string $currency): int
    {
        return (int) ($this->rates->currency($currency)['exponent'] ?? 2);
    }
}
