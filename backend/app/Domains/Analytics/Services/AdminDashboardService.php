<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Repositories\AdminDashboardRepository;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Services\McpAnalyticsReader;
use App\Domains\Mcp\Services\McpOperationsReader;
use App\Domains\Orders\Models\Order;
use App\Domains\Shared\ValueObjects\Money;
use Carbon\CarbonImmutable;

/**
 * The admin dashboard (M14, spec Phase 9.1, Figma "Admin Dashboard"). Revenue, profit and top
 * products come from the settled-order rollups the MCP readers already serve
 * (`order_item_financials`); counts come from {@see AdminDashboardRepository}. Each block is
 * included only when the viewer holds its permission (profit needs `reports.profit`, supplier
 * balances `suppliers.view`, gateways `payments.view`). Amounts are formatted here; chart
 * geometry is computed here so the page draws plain SVG without script.
 */
class AdminDashboardService
{
    public function __construct(
        private AdminDashboardRepository $repository,
        private McpAnalyticsReader $analytics,
        private McpOperationsReader $operations,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $rates,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $viewer, int $days): array
    {
        $to = CarbonImmutable::now()->endOfDay();
        $from = $to->subDays($days - 1)->startOfDay();
        $previousTo = $from->subSecond();
        $previousFrom = $previousTo->subDays($days - 1)->startOfDay();
        $base = $this->rates->baseCurrency();

        $sales = $this->analytics->salesSummary($from, $to, 'day');
        $previousSales = $this->analytics->salesSummary($previousFrom, $previousTo, 'none');
        $orders = $this->repository->ordersPlaced($from, $to);
        $previousOrders = $this->repository->ordersPlaced($previousFrom, $previousTo);
        $customers = $this->repository->newCustomers($from, $to);
        $previousCustomers = $this->repository->newCustomers($previousFrom, $previousTo);
        $failed = $this->repository->failedPayments($from, $to);
        $previousFailed = $this->repository->failedPayments($previousFrom, $previousTo);
        $open = $this->repository->openOrders(CarbonImmutable::now()->subHour());

        $kpis = [
            ['key' => 'revenue', 'value' => $this->format($sales['totals']['net_revenue']['amount'] ?? '0', $base), 'delta' => $this->delta($sales['totals']['net_revenue']['amount'] ?? '0', $previousSales['totals']['net_revenue']['amount'] ?? '0'), 'good' => 'up', 'series' => $this->series($sales['periods'], 'net_revenue')],
            ['key' => 'orders', 'value' => (string) $orders, 'delta' => $this->delta((string) $orders, (string) $previousOrders), 'good' => 'up', 'series' => []],
            ['key' => 'customers', 'value' => (string) $customers, 'delta' => $this->delta((string) $customers, (string) $previousCustomers), 'good' => 'up', 'series' => []],
        ];

        $profit = null;

        if ($viewer->can('reports.profit')) {
            $profit = $this->analytics->profitSummary($from, $to, 'day');
            $previousProfit = $this->analytics->profitSummary($previousFrom, $previousTo, 'none');
            $kpis[] = ['key' => 'profit', 'value' => $this->format($profit['totals']['net_profit']['amount'] ?? '0', $base), 'delta' => $this->delta($profit['totals']['net_profit']['amount'] ?? '0', $previousProfit['totals']['net_profit']['amount'] ?? '0'), 'good' => 'up', 'series' => $this->series($profit['periods'], 'net_profit')];
        }

        $kpis[] = ['key' => 'pending', 'value' => (string) $open['open'], 'delta' => null, 'note' => trans_choice('admin.dashboard.stuck', $open['stuck'], ['count' => $open['stuck']]), 'good' => 'down', 'series' => []];
        $kpis[] = ['key' => 'failed', 'value' => (string) $failed, 'delta' => $this->delta((string) $failed, (string) $previousFailed), 'good' => 'down', 'series' => []];

        return [
            'days' => $days,
            'currency' => $base,
            'kpis' => $kpis,
            'revenue_chart' => $this->chart($sales['periods'], 'net_revenue', $base),
            'profit_chart' => $profit === null ? null : $this->chart($profit['periods'], 'net_profit', $base),
            'supplier_balances' => $viewer->can('suppliers.view') ? $this->supplierBalances() : null,
            'gateways' => $viewer->can('payments.view') ? $this->gateways($from, $to) : null,
            'top_products' => array_map(fn (array $product): array => [
                'name' => $product['name'],
                'units' => $product['units_count'],
                'revenue' => $this->format($product['revenue']['amount'] ?? '0', $base),
            ], $this->analytics->topSellingProducts($from, $to, 5)['products']),
            'recent_orders' => $viewer->can('orders.view') ? $this->repository->recentOrders(5)->map(fn (Order $order): array => [
                'order' => $order,
                'number' => $order->order_number,
                'status' => $order->status->value,
                'total' => $this->format((string) $order->display_total, (string) $order->display_currency),
            ])->all() : null,
        ];
    }

    /**
     * @return list<array{name: string, balance: ?string, low: bool, known: bool, threshold: ?string}>
     */
    private function supplierBalances(): array
    {
        return array_map(fn (array $supplier): array => [
            'name' => $supplier['supplier'],
            'balance' => $supplier['balance'] === null ? null : $this->format($supplier['balance']['amount'], $supplier['balance']['currency']),
            'threshold' => $supplier['low_balance_threshold'] === null ? null : $this->format($supplier['low_balance_threshold']['amount'], $supplier['low_balance_threshold']['currency']),
            'low' => $supplier['is_low'],
            'known' => $supplier['balance_known'],
        ], $this->operations->supplierBalances()['suppliers']);
    }

    /**
     * @return list<array{name: string, currency: string, rate: ?string, finished: int}>
     */
    private function gateways(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->repository->gatewayOutcomes($from, $to)->map(static fn (array $row): array => [
            'name' => $row['gateway'],
            'currency' => $row['currency'],
            'finished' => $row['finished'],
            'rate' => $row['finished'] === 0 ? null : bcdiv(bcmul((string) $row['succeeded'], '100', 4), (string) $row['finished'], 1).'%',
        ])->values()->all();
    }

    /**
     * Change against the previous window in percent, one decimal; null when there is nothing to
     * compare with.
     *
     * @return array{value: string, direction: string}|null
     */
    private function delta(string $current, string $previous): ?array
    {
        if (bccomp($previous, '0', 6) === 0) {
            return null;
        }

        $change = bcdiv(bcmul(bcsub($current, $previous, 6), '100', 6), $previous, 1);
        $direction = bccomp($change, '0', 1);

        return ['value' => ltrim($change, '-').'%', 'direction' => $direction > 0 ? 'up' : ($direction < 0 ? 'down' : 'flat')];
    }

    /**
     * @param  list<array<string, mixed>>  $periods
     * @return list<string>
     */
    private function series(array $periods, string $field): array
    {
        return array_map(static fn (array $period): string => (string) ($period[$field]['amount'] ?? '0'), $periods);
    }

    /**
     * A bar chart in a 600 x 200 box: one bar per period, heights relative to the largest value
     * (negative values draw as zero), with three y-axis labels.
     *
     * @param  list<array<string, mixed>>  $periods
     * @return array{bars: list<array{x: string, y: string, w: string, h: string, label: string, value: string}>, ticks: list<array{y: string, label: string}>, empty: bool}
     */
    private function chart(array $periods, string $field, string $currency): array
    {
        $values = array_map(static fn (array $period): string => (string) ($period[$field]['amount'] ?? '0'), $periods);
        $max = array_reduce($values, static fn (string $carry, string $value): string => bccomp($value, $carry, 6) > 0 ? $value : $carry, '0');
        $count = max(count($values), 1);
        $slot = bcdiv('600', (string) $count, 4);
        $width = bcmul($slot, '0.7', 2);
        $bars = [];

        foreach ($values as $index => $value) {
            $height = bccomp($max, '0', 6) === 0 || bccomp($value, '0', 6) <= 0 ? '0' : bcdiv(bcmul($value, '190', 6), $max, 2);
            $bars[] = [
                'x' => bcadd(bcmul($slot, (string) $index, 4), bcmul($slot, '0.15', 4), 2),
                'y' => bcsub('200', $height, 2),
                'w' => $width,
                'h' => $height,
                'label' => (string) ($periods[$index]['period'] ?? ''),
                'value' => $this->format($value, $currency),
            ];
        }

        return [
            'bars' => $bars,
            'ticks' => [
                ['y' => '10', 'label' => $this->format($max, $currency)],
                ['y' => '105', 'label' => $this->format(bcdiv($max, '2', 6), $currency)],
                ['y' => '200', 'label' => $this->format('0', $currency)],
            ],
            'empty' => bccomp($max, '0', 6) === 0,
        ];
    }

    private function format(string $amount, string $currency): string
    {
        return $this->converter->present(Money::of($amount, $currency))['formatted'];
    }
}
