<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Repositories\McpAnalyticsRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Analytics and marketing reads for MCP. Every figure comes from stored data; where the store
 * does not collect the data yet, the result says `unavailable` with the reason instead of a number.
 */
class McpAnalyticsReader
{
    public function __construct(private McpAnalyticsRepository $analytics, private ExchangeRateRegistry $rates) {}

    /**
     * @return array<string, mixed>
     */
    public function salesSummary(CarbonImmutable $from, CarbonImmutable $to, string $groupBy): array
    {
        $base = $this->rates->baseCurrency();
        $rows = $this->analytics->financialsByPeriod($from, $to, $groupBy);

        $periods = $rows->map(static fn (array $row): array => [
            'period' => (string) $row['period'],
            'orders_count' => (int) $row['orders_count'],
            'units_count' => (int) $row['units_count'],
            'revenue' => McpFormat::money($row['revenue_base'], $base),
            'refunded' => McpFormat::money($row['refunded_base'], $base),
            'net_revenue' => McpFormat::money(bcsub((string) $row['revenue_base'], (string) $row['refunded_base'], 6), $base),
        ])->values()->all();

        $revenue = $this->sum($rows, 'revenue_base');
        $refunded = $this->sum($rows, 'refunded_base');

        return [
            ...$this->window($from, $to, $groupBy),
            'source' => 'order_item_financials',
            'basis' => 'Recognised when an order settles (completed, partially fulfilled or refunded); unsettled orders are not included.',
            'totals' => [
                'orders_count' => (int) $rows->sum('orders_count'),
                'units_count' => (int) $rows->sum('units_count'),
                'revenue' => McpFormat::money($revenue, $base),
                'refunded' => McpFormat::money($refunded, $base),
                'net_revenue' => McpFormat::money(bcsub($revenue, $refunded, 6), $base),
            ],
            'periods' => $periods,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profitSummary(CarbonImmutable $from, CarbonImmutable $to, string $groupBy): array
    {
        $base = $this->rates->baseCurrency();
        $rows = $this->analytics->financialsByPeriod($from, $to, $groupBy);

        $figures = static fn (array $row): array => [
            'revenue' => McpFormat::money($row['revenue_base'], $base),
            'refunded' => McpFormat::money($row['refunded_base'], $base),
            'supplier_cost' => McpFormat::money($row['supplier_cost_base'], $base),
            'gateway_fees' => McpFormat::money($row['gateway_fee_base'], $base),
            'fx_cost' => McpFormat::money($row['fx_cost_base'], $base),
            'other_cost' => McpFormat::money($row['other_cost_base'], $base),
            'gross_profit' => McpFormat::money($row['gross_profit_base'], $base),
            'net_profit' => McpFormat::money($row['net_profit_base'], $base),
            'net_margin_pct' => McpFormat::percent($row['net_profit_base'], bcsub((string) $row['revenue_base'], (string) $row['refunded_base'], 6)),
        ];

        $totals = [];

        foreach (['revenue_base', 'refunded_base', 'supplier_cost_base', 'gateway_fee_base', 'fx_cost_base', 'other_cost_base', 'gross_profit_base', 'net_profit_base'] as $column) {
            $totals[$column] = $this->sum($rows, $column);
        }

        return [
            ...$this->window($from, $to, $groupBy),
            'source' => 'order_item_financials',
            'basis' => 'Recognised when an order settles; gateway fees may be estimates until settlement data arrives.',
            'estimated_fee_rows' => (int) $rows->sum('estimated_fee_rows'),
            'totals' => $figures($totals),
            'periods' => $rows->map(static fn (array $row): array => ['period' => (string) $row['period'], ...$figures($row)])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function topSellingProducts(CarbonImmutable $from, CarbonImmutable $to, int $limit): array
    {
        $base = $this->rates->baseCurrency();

        return [
            ...$this->window($from, $to),
            'source' => 'order_item_financials',
            'ranked_by' => 'revenue',
            'products' => $this->analytics->topProductsByRevenue($from, $to, $limit)->map(static fn (array $row): array => [
                'product_id' => $row['public_id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'units_count' => (int) $row['units_count'],
                'orders_count' => (int) $row['orders_count'],
                'revenue' => McpFormat::money($row['revenue_base'], $base),
                'refunded' => McpFormat::money($row['refunded_base'], $base),
                'net_profit' => McpFormat::money($row['net_profit_base'], $base),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function supplierPerformance(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $base = $this->rates->baseCurrency();

        return [
            ...$this->window($from, $to),
            'source' => 'supplier_transactions, order_item_financials',
            'suppliers' => $this->analytics->supplierAttempts($from, $to)->map(static fn (array $row): array => [
                'supplier' => $row['name'],
                'adapter_key' => $row['adapter_key'],
                'attempts' => (int) $row['attempts'],
                'succeeded' => (int) $row['succeeded'],
                'rejected' => (int) $row['rejected'],
                'not_accepted_verified' => (int) $row['not_accepted_verified'],
                'unresolved' => (int) $row['unresolved'],
                'fallback_attempts' => (int) $row['fallback_attempts'],
                'success_rate_pct' => McpFormat::percent($row['succeeded'], $row['attempts']),
                'avg_seconds_to_success' => $row['avg_seconds_to_success'] === null ? null : (int) bcadd((string) $row['avg_seconds_to_success'], '0.5', 0),
                'recognised_supplier_cost' => McpFormat::money($row['supplier_cost_base'] ?? '0', $base),
            ])->values()->all(),
        ];
    }

    /**
     * Visitor conversion needs session data from GA4 (M18), which is not collected yet. The
     * checkout-to-payment rate is real: it is computed from orders placed and paid.
     *
     * @return array<string, mixed>
     */
    public function conversionMetrics(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $orders = $this->analytics->placedAndPaid($from, $to);

        return [
            ...$this->window($from, $to),
            'status' => 'partial',
            'unavailable' => [
                [
                    'metric' => 'visitor_conversion_rate',
                    'status' => 'unavailable',
                    'reason' => 'Sessions, product views and add-to-cart events are not collected yet. GA4 integration is milestone M18; until then there is no visitor denominator.',
                ],
                [
                    'metric' => 'cart_to_checkout_rate',
                    'status' => 'unavailable',
                    'reason' => 'Checkout starts are not recorded as events yet (GA4, milestone M18).',
                ],
            ],
            'checkout_to_payment' => [
                'status' => 'available',
                'source' => 'orders',
                'orders_placed' => $orders['placed'],
                'orders_paid' => $orders['paid'],
                'payment_rate_pct' => McpFormat::percent($orders['paid'], $orders['placed']),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function bestSellingProducts(CarbonImmutable $from, CarbonImmutable $to, int $limit): array
    {
        $base = $this->rates->baseCurrency();

        return [
            ...$this->window($from, $to),
            'source' => 'order_items on paid orders',
            'ranked_by' => 'units',
            'products' => $this->analytics->bestSellersByUnits($from, $to, $limit)->map(static fn (array $row): array => [
                'product_id' => $row['public_id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'units_count' => (int) $row['units_count'],
                'orders_count' => (int) $row['orders_count'],
                'sales' => McpFormat::money($row['sales_base'], $base),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function underperformingProducts(CarbonImmutable $from, CarbonImmutable $to, int $limit): array
    {
        $base = $this->rates->baseCurrency();

        return [
            ...$this->window($from, $to),
            'source' => 'active products and order_items on paid orders',
            'ranked_by' => 'fewest units',
            'products' => $this->analytics->underperformingProducts($from, $to, $limit)->map(static fn (array $row): array => [
                'product_id' => $row['public_id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'units_count' => (int) $row['units_count'],
                'sales' => McpFormat::money($row['sales_base'], $base),
                'lifetime_sales_count' => (int) $row['sales_count'],
                'published_at' => $row['published_at'] === null ? null : CarbonImmutable::parse($row['published_at'])->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function couponPerformance(CarbonImmutable $from, CarbonImmutable $to, ?string $code): array
    {
        $base = $this->rates->baseCurrency();

        return [
            ...$this->window($from, $to),
            'source' => 'coupon_redemptions (redeemed, i.e. paid orders)',
            'coupons' => $this->analytics->couponPerformance($from, $to, $code)->map(static fn (array $row): array => [
                'code' => $row['code'],
                'type' => $row['type'],
                'value' => bcadd((string) $row['value'], '0', 2),
                'is_active' => (bool) $row['is_active'],
                'created_by' => $row['created_by_type'],
                'usage_limit' => $row['usage_limit'] === null ? null : (int) $row['usage_limit'],
                'used_count' => (int) $row['used_count'],
                'redemptions' => (int) $row['redemptions'],
                'discount' => McpFormat::money($row['discount_base'], $base),
                'order_revenue' => McpFormat::money($row['order_revenue_base'], $base),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerPurchaseTrends(CarbonImmutable $from, CarbonImmutable $to, string $groupBy): array
    {
        $base = $this->rates->baseCurrency();

        return [
            ...$this->window($from, $to, $groupBy),
            'source' => 'paid orders (aggregates only, no customer identifiers)',
            'periods' => $this->analytics->purchaseTrends($from, $to, $groupBy)->map(static fn (array $row): array => [
                'period' => (string) $row['period'],
                'orders_count' => (int) $row['orders_count'],
                'revenue' => McpFormat::money($row['revenue_base'], $base),
                'average_order_value' => McpFormat::money($row['average_order_base'], $base),
                'guest_orders' => (int) $row['guest_orders'],
                'registered_orders' => (int) $row['registered_orders'],
                'repeat_orders' => (int) $row['repeat_orders'],
                'distinct_registered_buyers' => (int) $row['distinct_registered_buyers'],
            ])->values()->all(),
            'top_countries' => $this->analytics->paidOrdersByCountry($from, $to, 10)->all(),
        ];
    }

    /**
     * @return array{from: string, to: string, currency: string, group_by?: string}
     */
    private function window(CarbonImmutable $from, CarbonImmutable $to, ?string $groupBy = null): array
    {
        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'currency' => $this->rates->baseCurrency(),
            ...($groupBy === null ? [] : ['group_by' => $groupBy]),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    private function sum(Collection $rows, string $column): string
    {
        return $rows->reduce(static fn (string $carry, array $row): string => bcadd($carry, (string) ($row[$column] ?? '0'), 6), '0');
    }
}
