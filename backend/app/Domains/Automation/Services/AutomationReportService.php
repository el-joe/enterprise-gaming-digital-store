<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Repositories\AutomationReportRepository;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Services\McpAnalyticsReader;
use App\Domains\Mcp\Services\McpCatalogReader;
use App\Domains\Mcp\Services\McpFormat;
use Carbon\CarbonImmutable;

/**
 * Daily and weekly reports for the A and B workflows (financial reports are in
 * FinancialReportService). Figures come from the
 * existing analytics readers (order_item_financials for revenue and profit, paid orders for unit
 * sales) and fixed aggregate queries. Where the store does not hold the data, the section says
 * `unavailable` with the reason; nothing is estimated or invented.
 */
class AutomationReportService
{
    public function __construct(
        private McpAnalyticsReader $analytics,
        private McpCatalogReader $catalog,
        private AutomationReportRepository $reports,
        private ExchangeRateRegistry $rates,
        private FinancialReportService $financial,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function sales(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->analytics->salesSummary($from, $to, 'none');
    }

    /**
     * @return array<string, mixed>
     */
    public function profit(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->analytics->profitSummary($from, $to, 'none');
    }

    /**
     * A11: the window against the window before it and the same window one week earlier.
     *
     * @return array<string, mixed>
     */
    public function revenueComparison(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $length = $to->diffInSeconds($from, true);
        $current = $this->analytics->salesSummary($from, $to, 'none')['totals'];
        $previous = $this->analytics->salesSummary($from->subSeconds($length + 1), $from->subSecond(), 'none')['totals'];
        $lastWeek = $this->analytics->salesSummary($from->subWeek(), $to->subWeek(), 'none')['totals'];

        $change = static fn (array $now, array $then): ?string => McpFormat::percent(
            bcsub((string) $now['net_revenue']['amount'], (string) $then['net_revenue']['amount'], 6),
            $then['net_revenue']['amount'],
        );

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'order_item_financials',
            'basis' => 'Recognised when an order settles; unsettled orders are not included.',
            'current' => $current,
            'previous_period' => [...$previous, 'net_revenue_change_pct' => $change($current, $previous)],
            'same_period_last_week' => [...$lastWeek, 'net_revenue_change_pct' => $change($current, $lastWeek)],
        ];
    }

    /**
     * B01.
     *
     * @return array<string, mixed>
     */
    public function weeklyBusiness(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'sales' => $this->analytics->salesSummary($from, $to, 'day'),
            'profit' => $this->analytics->profitSummary($from, $to, 'none')['totals'],
            'top_products' => $this->analytics->topSellingProducts($from, $to, 5)['products'],
            'retention' => $this->retention($from, $to),
            'conversion' => $this->analytics->conversionMetrics($from, $to),
        ];
    }

    /**
     * B02 (`by=game`, products) and B03 (`by=platform`).
     *
     * @return array<string, mixed>
     */
    public function bestSelling(CarbonImmutable $from, CarbonImmutable $to, string $by, int $limit): array
    {
        if ($by === 'game') {
            return [
                ...$this->analytics->bestSellingProducts($from, $to, $limit),
                'note' => 'The catalog has no separate games table; a game is the product it is sold as.',
            ];
        }

        $base = $this->rates->baseCurrency();

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'order_items on paid orders',
            'ranked_by' => 'units',
            'platforms' => $this->reports->platformsBySales($from, $to, $limit)->map(static fn (array $row): array => [
                'platform' => $row['name'],
                'slug' => $row['slug'],
                'units_count' => (int) $row['units_count'],
                'orders_count' => (int) $row['orders_count'],
                'sales' => McpFormat::money($row['sales_base'], $base),
            ])->values()->all(),
        ];
    }

    /**
     * B04.
     *
     * @return array<string, mixed>
     */
    public function profitableProducts(CarbonImmutable $from, CarbonImmutable $to, int $limit): array
    {
        $base = $this->rates->baseCurrency();

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'order_item_financials',
            'ranked_by' => 'net_profit',
            'products' => $this->reports->productsByProfit($from, $to, $limit)->map(static fn (array $row): array => [
                'product_id' => $row['public_id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'items_count' => (int) $row['items_count'],
                'revenue' => McpFormat::money($row['revenue_base'], $base),
                'refunded' => McpFormat::money($row['refunded_base'], $base),
                'net_profit' => McpFormat::money($row['net_profit_base'], $base),
                'net_margin_pct' => McpFormat::percent($row['net_profit_base'], bcsub((string) $row['revenue_base'], (string) $row['refunded_base'], 6)),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function underperforming(CarbonImmutable $from, CarbonImmutable $to, int $limit): array
    {
        return $this->analytics->underperformingProducts($from, $to, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    public function supplierPerformance(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->analytics->supplierPerformance($from, $to);
    }

    /**
     * B07.
     *
     * @return array<string, mixed>
     */
    public function gatewayPerformance(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'payments (attempts created in the window), order_item_financials (fees)',
            'gateways' => $this->attemptRows($from, $to),
            'fees' => $this->financial->gatewayFees($from, $to)['gateways'],
            'unavailable' => [[
                'metric' => 'provider_latency_and_decline_reasons',
                'status' => 'unavailable',
                'reason' => 'Provider response times and decline reason codes are not stored per attempt, so they cannot be compared.',
            ]],
        ];
    }

    /**
     * B08.
     *
     * @return array<string, mixed>
     */
    public function retention(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $retention = $this->reports->retention($from, $to);

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'paid orders of registered customers',
            'registered_buyers' => $retention['buyers'],
            'returning_buyers' => $retention['returning'],
            'new_buyers' => $retention['new'],
            'returning_rate_pct' => McpFormat::percent($retention['returning'], $retention['buyers']),
            'guest_orders' => $retention['guest_orders'],
            'unavailable' => [[
                'metric' => 'guest_retention',
                'status' => 'unavailable',
                'reason' => 'Guest orders are not linked to an account, so returning guests cannot be identified.',
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function couponPerformance(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->analytics->couponPerformance($from, $to, null);
    }

    /**
     * B10: products whose current price leaves less than the threshold margin, as candidates for
     * a human pricing review. Demand elasticity and competitor prices are not collected.
     *
     * @return array<string, mixed>
     */
    public function pricingRecommendations(string $thresholdPct, int $limit): array
    {
        $lowMargin = $this->catalog->lowMargin($thresholdPct, 1, $limit);

        return [
            'source' => 'pricing engine quotes against current supplier cost',
            'threshold_pct' => $thresholdPct,
            'scanned_variants' => $lowMargin['scanned'],
            'cost_unknown' => $lowMargin['cost_unknown'],
            'scan_truncated' => $lowMargin['truncated'],
            'candidates' => array_values($lowMargin['page']->items()),
            'unavailable' => [
                ['metric' => 'competitor_prices', 'status' => 'unavailable', 'reason' => 'No competitor price source is configured or licensed.'],
                ['metric' => 'price_elasticity', 'status' => 'unavailable', 'reason' => 'Price change history against sales volume is not collected, so demand response cannot be estimated.'],
            ],
            'note' => 'Recommendations are drafts for staff review (POST /suggestions, type pricing); no price is changed automatically.',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function attemptRows(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->reports->paymentAttemptsByGateway($from, $to)->map(static fn (array $row): array => [
            'gateway' => $row['key'],
            'name' => $row['name'],
            'attempts' => (int) $row['attempts'],
            'succeeded' => (int) $row['succeeded'],
            'failed' => (int) $row['failed'],
            'expired_or_cancelled' => (int) $row['abandoned'],
            'success_rate_pct' => McpFormat::percent($row['succeeded'], $row['attempts']),
            'failure_rate_pct' => McpFormat::percent($row['failed'], $row['attempts']),
        ])->values()->all();
    }
}
