<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Repositories\AutomationReportRepository;
use App\Domains\Mcp\Services\McpFormat;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * System monitoring reads for the F workflows: queues, exceptions, scheduler heartbeats,
 * database, recorded latency and webhook delivery. Each result carries `alert`; sources that do
 * not exist are reported as `unavailable`.
 */
class SystemMonitorService
{
    public function __construct(private AutomationReportRepository $reports, private SchedulerMonitor $scheduler) {}

    /**
     * F02.
     *
     * @return array<string, mixed>
     */
    public function queues(int $windowMinutes): array
    {
        $since = CarbonImmutable::now()->subMinutes($windowMinutes);
        $queues = $this->reports->queues($since);
        $now = CarbonImmutable::now()->getTimestamp();

        $pending = [];

        foreach ($queues['pending_by_queue'] as $queue => $row) {
            $pending[$queue] = ['jobs' => $row['jobs'], 'oldest_wait_seconds' => $row['oldest_available_at'] === null ? null : max(0, $now - $row['oldest_available_at'])];
        }

        return [
            'driver' => (string) config('queue.default'),
            'window_minutes' => $windowMinutes,
            'alert' => array_sum($queues['failed_since']) > 0,
            'failed_in_window' => $queues['failed_since'],
            'failed_total' => $queues['failed_total'],
            'pending_by_queue' => $pending,
            'note' => (string) config('queue.default') === 'database' ? null : 'Pending counts read the database `jobs` table; with the Redis driver they are not visible here, only failed jobs are.',
        ];
    }

    /**
     * F03. Laravel exceptions are written to log files only; there is no exception store.
     *
     * @return array<string, mixed>
     */
    public function exceptions(): array
    {
        return [
            'status' => 'unavailable',
            'alert' => false,
            'reason' => 'Unhandled exceptions are written to the log files only. No exception store or error tracker is configured, so they cannot be counted through the API. Failed queue jobs, which carry their exceptions, are reported by GET /monitor/queues.',
        ];
    }

    /**
     * F04.
     *
     * @return array<string, mixed>
     */
    public function scheduler(): array
    {
        $report = $this->scheduler->report();

        return ['alert' => $report['status'] !== 'ok', ...$report];
    }

    /**
     * F05: a Laravel-computed summary; no query access is given.
     *
     * @return array<string, mixed>
     */
    public function database(): array
    {
        $started = hrtime(true);

        try {
            DB::selectOne('SELECT 1 AS ok');
            $reachable = true;
        } catch (Throwable) {
            $reachable = false;
        }

        $pingMs = intdiv(hrtime(true) - $started, 1_000_000);
        $status = $reachable ? $this->serverStatus() : [];
        $connected = isset($status['Threads_connected']) ? (int) $status['Threads_connected'] : null;
        $max = isset($status['max_connections']) ? (int) $status['max_connections'] : null;
        $usagePct = $connected !== null && $max !== null && $max > 0 ? McpFormat::percent($connected, $max) : null;

        return [
            'alert' => ! $reachable || ($usagePct !== null && bccomp($usagePct, '80', 2) >= 0),
            'reachable' => $reachable,
            'ping_ms' => $pingMs,
            'connections' => $connected === null ? ['status' => 'unavailable', 'reason' => 'The database user cannot read server status.'] : [
                'connected' => $connected,
                'max_connections' => $max,
                'max_used' => isset($status['Max_used_connections']) ? (int) $status['Max_used_connections'] : null,
                'usage_pct' => $usagePct,
            ],
            'slow_queries_total' => isset($status['Slow_queries']) ? (int) $status['Slow_queries'] : null,
            'replication' => ['status' => 'unavailable', 'reason' => 'The deployment is a single MySQL server with no replica (DEPLOYMENT.md).'],
        ];
    }

    /**
     * F06: durations the application already records. Storefront request latency is not recorded.
     *
     * @return array<string, mixed>
     */
    public function latency(int $windowMinutes): array
    {
        return [
            'window_minutes' => $windowMinutes,
            'alert' => false,
            'recorded' => $this->reports->recordedLatency(CarbonImmutable::now()->subMinutes($windowMinutes)),
            'unavailable' => [[
                'metric' => 'storefront_and_admin_request_latency',
                'status' => 'unavailable',
                'reason' => 'Web request durations are not recorded (no APM or access-log ingestion). F01 checks that /up and the homepage answer from n8n, without timing them.',
            ]],
        ];
    }

    /**
     * F08.
     *
     * @return array<string, mixed>
     */
    public function webhooks(int $windowMinutes): array
    {
        $since = CarbonImmutable::now()->subMinutes($windowMinutes);
        $counts = $this->reports->webhookFailures($since);

        return [
            'window_minutes' => $windowMinutes,
            'alert' => $counts['payment_webhooks_failed'] + $counts['payment_webhooks_bad_signature'] + $counts['outbox_failed'] > 0,
            ...$counts,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function serverStatus(): array
    {
        try {
            $rows = [
                ...DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN ('Threads_connected', 'Max_used_connections', 'Slow_queries')"),
                ...DB::select("SHOW VARIABLES WHERE Variable_name = 'max_connections'"),
            ];
        } catch (Throwable) {
            return [];
        }

        $status = [];

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $status[(string) $values[0]] = (string) $values[1];
        }

        return $status;
    }
}
