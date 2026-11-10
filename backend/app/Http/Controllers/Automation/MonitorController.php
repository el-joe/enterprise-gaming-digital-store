<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Services\AutomationMonitorService;
use App\Domains\Automation\Services\SystemMonitorService;
use App\Http\Requests\Automation\AutomationQueryRequest;
use Illuminate\Http\JsonResponse;

/**
 * Monitoring reads (A, E and F workflows). Each result carries `alert`; n8n only routes it.
 */
class MonitorController extends AutomationController
{
    public function __construct(private AutomationMonitorService $monitor, private SystemMonitorService $system) {}

    public function failedOrders(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->monitor->failedOrders($request->since(15)));
    }

    public function pendingOrders(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->monitor->pendingOrders($request->intOr('older_than', 60)));
    }

    public function supplierBalances(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->monitor->supplierBalances((string) ($request->validated('level') ?? 'warning')));
    }

    public function supplierHealth(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->monitor->supplierHealth($request->validated('detail') === '1'));
    }

    public function paymentFailures(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->monitor->paymentFailures($request->intOr('window', 60)));
    }

    public function lowMarginProducts(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->monitor->lowMarginProducts($request->thresholdPct((string) config('automation.low_margin_threshold_pct')), $request->intOr('limit', 50)));
    }

    public function marginAlerts(AutomationQueryRequest $request): JsonResponse
    {
        [$from, $to] = $request->window('last_hour');

        return $this->success($this->monitor->marginAlerts($from, $to, $request->thresholdPct('0')));
    }

    public function queues(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->system->queues($request->intOr('window', 5)));
    }

    public function exceptions(): JsonResponse
    {
        return $this->success($this->system->exceptions());
    }

    public function scheduler(): JsonResponse
    {
        return $this->success($this->system->scheduler());
    }

    public function database(): JsonResponse
    {
        return $this->success($this->system->database());
    }

    public function latency(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->system->latency($request->intOr('window', 5)));
    }

    public function webhooks(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->system->webhooks($request->intOr('window', 15)));
    }
}
