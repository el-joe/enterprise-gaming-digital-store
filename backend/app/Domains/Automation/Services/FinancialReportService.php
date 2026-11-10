<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Repositories\AutomationReportRepository;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Services\McpAnalyticsReader;
use App\Domains\Mcp\Services\McpFormat;
use Carbon\CarbonImmutable;

/**
 * Financial reports for the E workflows: revenue and direct costs, reconciliation results,
 * gateway fees, risk flags and refunds. Read-only; figures come from stored data, and data the
 * store does not hold is reported as `unavailable`.
 */
class FinancialReportService
{
    public function __construct(
        private McpAnalyticsReader $analytics,
        private AutomationReportRepository $reports,
        private ExchangeRateRegistry $rates,
    ) {}

    /**
     * E01: revenue and direct costs from settled orders. Operating expenses are not recorded.
     *
     * @return array<string, mixed>
     */
    public function expenses(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $profit = $this->analytics->profitSummary($from, $to, 'none');

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'order_item_financials',
            'revenue' => $profit['totals']['revenue'],
            'refunded' => $profit['totals']['refunded'],
            'direct_costs' => [
                'supplier_cost' => $profit['totals']['supplier_cost'],
                'gateway_fees' => $profit['totals']['gateway_fees'],
                'fx_cost' => $profit['totals']['fx_cost'],
                'other_cost' => $profit['totals']['other_cost'],
            ],
            'estimated_fee_rows' => $profit['estimated_fee_rows'],
            'net_profit' => $profit['totals']['net_profit'],
            'unavailable' => [[
                'metric' => 'operating_expenses',
                'status' => 'unavailable',
                'reason' => 'Operating expenses (hosting, staff, marketing spend) are not recorded in the store.',
            ]],
        ];
    }

    /**
     * E02: what the scheduler's or n8n's daily reconciliation found. Reading this changes nothing.
     *
     * @return array<string, mixed>
     */
    public function reconciliation(CarbonImmutable $since): array
    {
        $reconciliation = $this->reports->reconciliation($since);

        return [
            'since' => $since->toIso8601String(),
            'source' => 'reconciliation_runs, reconciliation_items',
            'runs' => $reconciliation['runs']->map(static fn (array $run): array => [
                'kind' => $run['kind'],
                'subject' => $run['subject_key'],
                'status' => $run['status'],
                'checked' => (int) $run['checked'],
                'mismatches' => (int) $run['mismatches'],
                'window_start' => CarbonImmutable::parse($run['window_start'])->toIso8601String(),
                'window_end' => CarbonImmutable::parse($run['window_end'])->toIso8601String(),
                'finished_at' => $run['finished_at'] === null ? null : CarbonImmutable::parse($run['finished_at'])->toIso8601String(),
            ])->values()->all(),
            'open_items_by_type' => $reconciliation['open_items'],
            'open_items_total' => array_sum($reconciliation['open_items']),
        ];
    }

    /**
     * E04.
     *
     * @return array<string, mixed>
     */
    public function gatewayFees(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $base = $this->rates->baseCurrency();

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'order_item_financials',
            'gateways' => $this->reports->feesByGateway($from, $to)->map(static fn (array $row): array => [
                'gateway' => $row['gateway'],
                'orders_count' => (int) $row['orders_count'],
                'revenue' => McpFormat::money($row['revenue_base'], $base),
                'gateway_fees' => McpFormat::money($row['gateway_fee_base'], $base),
                'fx_cost' => McpFormat::money($row['fx_cost_base'], $base),
                'fee_pct_of_revenue' => McpFormat::percent($row['gateway_fee_base'], $row['revenue_base']),
                'estimated_fee_rows' => (int) $row['estimated_rows'],
                'rows' => (int) $row['rows_count'],
            ])->values()->all(),
        ];
    }

    /**
     * E06: orders the risk engine held or blocked, and disputes opened.
     *
     * @return array<string, mixed>
     */
    public function unusualTransactions(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $base = $this->rates->baseCurrency();
        $flags = $this->reports->riskFlags($from, $to, 50);

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'risk_assessments, payment_disputes',
            'flagged_count' => $flags->count(),
            'flagged' => $flags->map(static function (array $row) use ($base): array {
                $signals = json_decode((string) $row['signals'], true);

                return [
                    'order_number' => $row['order_number'],
                    'order_status' => $row['status'],
                    'total' => McpFormat::money($row['total_base'], $base),
                    'score' => (int) $row['score'],
                    'decision' => $row['decision'],
                    'signals' => is_array($signals) ? array_values(array_map(static fn (mixed $signal): string => is_array($signal) ? (string) ($signal['code'] ?? '') : (string) $signal, $signals)) : [],
                    'assessed_at' => CarbonImmutable::parse($row['created_at'])->toIso8601String(),
                ];
            })->values()->all(),
            'disputes_opened' => $this->reports->disputesOpened($from, $to),
        ];
    }

    /**
     * E07.
     *
     * @return array<string, mixed>
     */
    public function refunds(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $base = $this->rates->baseCurrency();
        $rows = $this->reports->refundsByStatus($from, $to);

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'source' => 'refunds',
            'refunds_count' => (int) $rows->sum('refunds_count'),
            'amount' => McpFormat::money($rows->reduce(static fn (string $carry, array $row): string => bcadd($carry, (string) $row['amount_base'], 6), '0'), $base),
            'breakdown' => $rows->map(static fn (array $row): array => [
                'status' => $row['status'],
                'reason' => $row['reason'],
                'destination' => $row['destination'],
                'refunds_count' => (int) $row['refunds_count'],
                'amount' => McpFormat::money($row['amount_base'], $base),
            ])->values()->all(),
        ];
    }
}
