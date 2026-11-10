<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Repositories\AutomationReportRepository;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Shared\Exceptions\ApplicationException;
use Carbon\CarbonImmutable;

/**
 * Inputs for the AI marketing workflows (section C): aggregated internal sales data and public
 * catalog facts only. No customer data, costs or supplier details. External trend sources are not
 * connected, which every trending result states.
 */
class MarketingDataService
{
    public function __construct(private AutomationReportRepository $reports, private ExchangeRateRegistry $rates) {}

    /**
     * @return array<string, mixed>
     */
    public function trending(int $days, int $limit): array
    {
        $to = CarbonImmutable::now();
        $from = $to->subDays($days);
        $base = $this->rates->baseCurrency();

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'compared_with' => 'the previous '.$days.' days',
            'source' => 'order_items on paid orders (internal sales only)',
            'products' => $this->reports->productMomentum($from, $to, $limit)->map(static fn (array $row): array => [
                'product_id' => $row['public_id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'platform' => $row['platform'],
                'units_current' => (int) $row['units_current'],
                'units_previous' => (int) $row['units_previous'],
                'change_pct' => McpFormat::percent((int) $row['units_current'] - (int) $row['units_previous'], $row['units_previous']),
            ])->values()->all(),
            'platforms' => $this->reports->platformsBySales($from, $to, $limit)->map(static fn (array $row): array => [
                'platform' => $row['name'],
                'units_count' => (int) $row['units_count'],
                'orders_count' => (int) $row['orders_count'],
                'sales' => McpFormat::money($row['sales_base'], $base),
            ])->values()->all(),
            'unavailable' => [[
                'metric' => 'external_market_trends',
                'status' => 'unavailable',
                'reason' => 'No external trend source (search trends, store charts) is connected or licensed; results reflect this store\'s own sales only.',
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productContext(string $publicId): array
    {
        $product = $this->reports->findProductForContext($publicId);

        if ($product === null) {
            throw new ApplicationException('No product has this id.', 'not_found', 404);
        }

        $base = $this->rates->baseCurrency();
        $now = CarbonImmutable::now();

        return [
            'product_id' => $product->public_id,
            'sku' => $product->sku,
            'name' => $product->name,
            'status' => $product->status->value,
            'platform' => $product->gamingPlatform?->name,
            'category' => $product->primaryCategory?->name,
            'delivery_type' => $product->delivery_type->value,
            'short_description' => $product->short_description,
            'description' => $product->description === null ? null : mb_substr(strip_tags($product->description), 0, 4000),
            'has_description' => trim((string) $product->description) !== '',
            'rating_avg' => (string) $product->rating_avg,
            'rating_count' => (int) $product->rating_count,
            'variants' => $product->productVariants->where('is_active', true)->map(static fn (ProductVariant $variant): array => [
                'sku' => $variant->sku,
                'title' => $variant->title,
                'denomination' => McpFormat::money($variant->denomination_value, $variant->denomination_currency),
                'price' => McpFormat::money($variant->price_base, $base),
                'availability' => $variant->availability->value,
            ])->values()->all(),
            'sales_last_30_days' => $this->reports->productSales($product->id, $now->subDays(30), $now),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productsMissingDescriptions(int $limit): array
    {
        return [
            'source' => 'active products with no description or short description',
            'products' => $this->reports->productsMissingDescriptions($limit)->map(static fn (array $row): array => [
                'product_id' => $row['public_id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'platform' => $row['platform'],
                'category' => $row['category'],
                'delivery_type' => $row['delivery_type'],
            ])->values()->all(),
        ];
    }
}
