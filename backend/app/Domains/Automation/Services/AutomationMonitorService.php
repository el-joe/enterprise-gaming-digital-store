<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Repositories\AutomationReportRepository;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Services\McpCatalogReader;
use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Mcp\Services\McpOperationsReader;
use App\Domains\Orders\Enums\OrderStatus;
use Carbon\CarbonImmutable;

/**
 * Monitoring reads for the A and E workflows (system checks for F are in SystemMonitorService). Each result has `alert` (true when the workflow
 * should notify) next to the figures it is based on, so the threshold logic lives in Laravel and
 * n8n only routes the message. Sources that do not exist are reported as `unavailable`.
 */
class AutomationMonitorService
{
    private const array STALLED_STATUSES = [OrderStatus::PendingVerification, OrderStatus::ManualReview, OrderStatus::SupplierPending, OrderStatus::Processing, OrderStatus::PaymentProcessing];

    public function __construct(
        private AutomationReportRepository $reports,
        private McpOperationsReader $operations,
        private McpCatalogReader $catalog,
        private AutomationReportService $reportService,
        private ExchangeRateRegistry $rates,
    ) {}

    /**
     * A03.
     *
     * @return array<string, mixed>
     */
    public function failedOrders(CarbonImmutable $since): array
    {
        $base = $this->rates->baseCurrency();
        $count = $this->reports->countOrdersInStatusSince([OrderStatus::Failed->value], $since);

        return [
            'since' => $since->toIso8601String(),
            'alert' => $count > 0,
            'failed_count' => $count,
            'orders' => $this->reports->ordersInStatusSince([OrderStatus::Failed->value], $since, 50)->map(static fn (array $row): array => [
                'order_number' => $row['order_number'],
                'order_id' => $row['public_id'],
                'failed_at' => CarbonImmutable::parse($row['status_changed_at'])->toIso8601String(),
                'total' => McpFormat::money($row['total_base'], $base),
                'is_guest' => $row['user_id'] === null,
            ])->values()->all(),
        ];
    }

    /**
     * A04: orders stuck in a waiting status for longer than the given minutes.
     *
     * @return array<string, mixed>
     */
    public function pendingOrders(int $olderThanMinutes): array
    {
        $before = CarbonImmutable::now()->subMinutes($olderThanMinutes);
        $stalled = $this->reports->stalledOrders(array_map(static fn (OrderStatus $status): string => $status->value, self::STALLED_STATUSES), $before, 50);

        return [
            'older_than_minutes' => $olderThanMinutes,
            'alert' => array_sum($stalled['counts']) > 0,
            'counts_by_status' => $stalled['counts'],
            'orders' => $stalled['orders']->map(static fn (array $row): array => [
                'order_number' => $row['order_number'],
                'order_id' => $row['public_id'],
                'status' => $row['status'],
                'since' => CarbonImmutable::parse($row['status_changed_at'])->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * A05 (`level=warning`, below each supplier's threshold) and E03 (`level=critical`, below the
     * threshold times the configured fraction). Balances are those cached by
     * suppliers:refresh-balances.
     *
     * @return array<string, mixed>
     */
    public function supplierBalances(string $level): array
    {
        $fraction = (string) config('automation.supplier_balance_critical_fraction');
        $balances = $this->operations->supplierBalances();

        $suppliers = array_map(static function (array $supplier) use ($level, $fraction): array {
            $threshold = $supplier['low_balance_threshold']['amount'] ?? null;
            $balance = $supplier['balance']['amount'] ?? null;
            $limit = $threshold === null ? null : ($level === 'critical' ? bcmul((string) $threshold, $fraction, 6) : (string) $threshold);

            return [
                ...$supplier,
                'alert_threshold' => $limit,
                'below_threshold' => $limit !== null && $balance !== null && bccomp((string) $balance, $limit, 6) === -1,
            ];
        }, $balances['suppliers']);

        $below = array_values(array_filter($suppliers, static fn (array $supplier): bool => $supplier['below_threshold']));

        return [
            'level' => $level,
            'source' => $balances['source'],
            'critical_fraction' => $level === 'critical' ? $fraction : null,
            'alert' => $below !== [],
            'below_threshold_count' => count($below),
            'unknown_balance_count' => count(array_filter($suppliers, static fn (array $supplier): bool => ! $supplier['balance_known'])),
            'suppliers' => $suppliers,
        ];
    }

    /**
     * A06 and F07.
     *
     * @return array<string, mixed>
     */
    public function supplierHealth(bool $detail): array
    {
        $status = $this->operations->supplierStatus(null);
        $down = array_values(array_filter($status['suppliers'], static fn (array $supplier): bool => $supplier['status'] === 'active' && ($supplier['health'] === 'down' || $supplier['circuit_open'])));

        return [
            'alert' => $down !== [],
            'down_count' => count($down),
            'down' => array_map(static fn (array $supplier): string => $supplier['supplier'], $down),
            'suppliers' => $detail ? $status['suppliers'] : array_map(static fn (array $supplier): array => [
                'supplier' => $supplier['supplier'],
                'status' => $supplier['status'],
                'health' => $supplier['health'],
                'circuit_open' => $supplier['circuit_open'],
            ], $status['suppliers']),
        ];
    }

    /**
     * A07: payment attempts per gateway in the last `window` minutes.
     *
     * @return array<string, mixed>
     */
    public function paymentFailures(int $windowMinutes): array
    {
        $to = CarbonImmutable::now();
        $from = $to->subMinutes($windowMinutes);
        $threshold = (string) config('automation.payment_failure_rate_alert_pct');
        $gateways = $this->reportService->attemptRows($from, $to);

        $spikes = array_values(array_filter($gateways, static fn (array $gateway): bool => $gateway['failed'] >= 3 && $gateway['failure_rate_pct'] !== null && bccomp($gateway['failure_rate_pct'], $threshold, 2) >= 0));

        return [
            'window_minutes' => $windowMinutes,
            'failure_rate_alert_pct' => $threshold,
            'minimum_failures_for_alert' => 3,
            'alert' => $spikes !== [],
            'spiking_gateways' => array_map(static fn (array $gateway): string => $gateway['gateway'], $spikes),
            'gateways' => $gateways,
        ];
    }

    /**
     * A08.
     *
     * @return array<string, mixed>
     */
    public function lowMarginProducts(string $thresholdPct, int $limit): array
    {
        $result = $this->catalog->lowMargin($thresholdPct, 1, $limit);
        $items = array_values($result['page']->items());

        return [
            'threshold_pct' => $thresholdPct,
            'alert' => $result['page']->total() > 0,
            'below_threshold_count' => $result['page']->total(),
            'scanned_variants' => $result['scanned'],
            'cost_unknown' => $result['cost_unknown'],
            'scan_truncated' => $result['truncated'],
            'variants' => $items,
        ];
    }

    /**
     * E05: settled items recognised in the window with a net margin below the threshold.
     *
     * @return array<string, mixed>
     */
    public function marginAlerts(CarbonImmutable $from, CarbonImmutable $to, string $thresholdPct): array
    {
        $base = $this->rates->baseCurrency();
        $items = $this->reports->itemsBelowMargin($from, $to, $thresholdPct, 50);

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'order_item_financials',
            'threshold_pct' => $thresholdPct,
            'alert' => $items->isNotEmpty(),
            'items' => $items->map(static fn (array $row): array => [
                'order_number' => $row['order_number'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'net_revenue' => McpFormat::money(bcsub((string) $row['revenue_base'], (string) $row['refunded_base'], 6), $base),
                'net_profit' => McpFormat::money($row['net_profit_base'], $base),
                'net_margin_pct' => McpFormat::percent($row['net_profit_base'], bcsub((string) $row['revenue_base'], (string) $row['refunded_base'], 6)),
                'recognized_at' => CarbonImmutable::parse($row['recognized_at'])->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
