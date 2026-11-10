<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Automation\Services\SchedulerMonitor;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Repositories\McpOperationsRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Enums\SupplierHealth;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * Supplier and system reads for MCP. Balances are the values last cached by
 * `suppliers:refresh-balances`; this never calls a supplier API.
 */
class McpOperationsReader
{
    public function __construct(
        private McpOperationsRepository $operations,
        private ExchangeRateRegistry $rates,
        private SchedulerMonitor $scheduler,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function supplierBalances(): array
    {
        return [
            'source' => 'cached by suppliers:refresh-balances; no live supplier call',
            'suppliers' => $this->operations->suppliers()->map(static fn (Supplier $supplier): array => [
                'supplier' => $supplier->name,
                'adapter_key' => $supplier->adapter_key,
                'status' => $supplier->status->value,
                'balance' => McpFormat::money($supplier->last_balance, $supplier->balance_currency),
                'balance_at' => McpFormat::iso($supplier->last_balance_at),
                'low_balance_threshold' => McpFormat::money($supplier->low_balance_threshold, $supplier->balance_currency),
                'is_low' => $supplier->last_balance !== null && $supplier->low_balance_threshold !== null
                    && bccomp((string) $supplier->last_balance, (string) $supplier->low_balance_threshold, 6) === -1,
                'balance_known' => $supplier->last_balance !== null,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function supplierStatus(?string $supplier): array
    {
        $suppliers = $this->suppliers($supplier);
        $checks = $this->operations->latestHealthChecks($suppliers->modelKeys());

        return [
            'suppliers' => $suppliers->map(static function (Supplier $row) use ($checks): array {
                $check = $checks->get($row->id);

                return [
                    'supplier' => $row->name,
                    'adapter_key' => $row->adapter_key,
                    'status' => $row->status->value,
                    'mode' => $row->mode?->value,
                    'health' => $row->health?->value,
                    'circuit_open' => $row->circuit_open_until !== null && $row->circuit_open_until->isFuture(),
                    'circuit_open_until' => McpFormat::iso($row->circuit_open_until),
                    'last_health_check' => $check === null ? null : [
                        'reachable' => (bool) $check->reachable,
                        'latency_ms' => $check->latency_ms,
                        'at' => McpFormat::iso($check->created_at),
                    ],
                ];
            })->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function syncStatus(?string $supplier): array
    {
        $suppliers = $this->suppliers($supplier);
        $runs = $this->operations->latestSyncRuns($suppliers->modelKeys());

        return [
            'suppliers' => $suppliers->map(static function (Supplier $row) use ($runs): array {
                $present = static function (string $type) use ($runs, $row): ?array {
                    $run = $runs->get($row->id.':'.$type);

                    return $run === null ? null : [
                        'status' => $run->status->value,
                        'triggered_by' => $run->triggered_by,
                        'items_seen' => $run->items_seen,
                        'items_created' => $run->items_created,
                        'items_updated' => $run->items_updated,
                        'items_failed' => $run->items_failed,
                        'has_error' => $run->error !== null && $run->error !== '',
                        'started_at' => McpFormat::iso($run->started_at),
                        'finished_at' => McpFormat::iso($run->finished_at),
                    ];
                };

                return ['supplier' => $row->name, 'adapter_key' => $row->adapter_key, 'catalog' => $present('catalog'), 'pricing' => $present('pricing')];
            })->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function systemHealth(): array
    {
        try {
            $database = $this->operations->databaseReachable();
        } catch (Throwable) {
            $database = false;
        }

        $suppliers = $this->operations->suppliers()->where('status', SupplierStatus::Active);
        $queue = $this->operations->queueCounts();

        $checks = [
            'database' => ['status' => $database ? 'ok' : 'down'],
            'queue' => [
                'status' => $queue['failed_jobs'] > 0 ? 'degraded' : 'ok',
                'driver' => (string) config('queue.default'),
                ...$queue,
            ],
            'suppliers' => [
                'status' => $suppliers->contains(static fn (Supplier $supplier): bool => $supplier->health === SupplierHealth::Down || ($supplier->circuit_open_until?->isFuture() ?? false)) ? 'degraded' : 'ok',
                'active' => $suppliers->count(),
                'by_health' => $suppliers->countBy(static fn (Supplier $supplier): string => $supplier->health?->value ?? 'unknown')->all(),
                'circuits_open' => $suppliers->filter(static fn (Supplier $supplier): bool => $supplier->circuit_open_until?->isFuture() ?? false)->count(),
            ],
            'exchange_rates' => $this->rateHealth(),
            'fulfilment' => [
                'status' => 'ok',
                'unresolved_supplier_attempts' => $this->operations->unresolvedSupplierAttempts(),
                'open_manual_tasks' => $this->operations->openManualTasks(),
            ],
            'scheduler' => $this->schedulerHealth(),
        ];

        $statuses = array_column($checks, 'status');

        return [
            'status' => in_array('down', $statuses, true) ? 'down' : (in_array('degraded', $statuses, true) ? 'degraded' : 'ok'),
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    /**
     * From scheduled_task_runs heartbeats (M17): late, failing or never-run tasks degrade it.
     *
     * @return array<string, mixed>
     */
    private function schedulerHealth(): array
    {
        $report = $this->scheduler->report();

        return [
            'status' => $report['status'],
            'source' => 'scheduled_task_runs',
            'tasks' => count($report['tasks']),
            'counts' => $report['counts'],
            'problems' => array_values(array_map(
                static fn (array $task): array => ['command' => $task['command'], 'state' => $task['state'], 'last_due_at' => $task['last_due_at'], 'last_started_at' => $task['last_run']['started_at'] ?? null],
                array_filter($report['tasks'], static fn (array $task): bool => in_array($task['state'], ['late', 'failing', 'never_run'], true)),
            )),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rateHealth(): array
    {
        $stale = [];
        $missing = [];
        $base = $this->rates->baseCurrency();

        foreach ($this->rates->activeCurrencies() as $currency) {
            if ($currency['code'] === $base) {
                continue;
            }

            try {
                if ($this->rates->resolve($currency['code'])->isStale) {
                    $stale[] = $currency['code'];
                }
            } catch (ExchangeRateMissing) {
                $missing[] = $currency['code'];
            }
        }

        return [
            'status' => $stale === [] && $missing === [] ? 'ok' : 'degraded',
            'base_currency' => $base,
            'stale' => $stale,
            'missing' => $missing,
        ];
    }

    /**
     * @return Collection<int, Supplier>
     */
    private function suppliers(?string $supplier): Collection
    {
        $suppliers = $this->operations->suppliers($supplier);

        if ($supplier !== null && $suppliers->isEmpty()) {
            throw new ApplicationException('No supplier matches.', 'not_found', 404);
        }

        return $suppliers;
    }
}
