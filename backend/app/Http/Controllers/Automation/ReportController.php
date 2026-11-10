<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Services\AutomationReportService;
use App\Domains\Automation\Services\FinancialReportService;
use App\Http\Requests\Automation\AutomationQueryRequest;
use Illuminate\Http\JsonResponse;

/**
 * Daily (A), weekly (B) and financial (E) reports. Read-only.
 */
class ReportController extends AutomationController
{
    public function __construct(private AutomationReportService $reports, private FinancialReportService $financial) {}

    public function sales(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->sales(...$request->window('yesterday')));
    }

    public function profit(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->profit(...$request->window('yesterday')));
    }

    public function revenueComparison(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->revenueComparison(...$request->window('yesterday')));
    }

    public function weeklyBusiness(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->weeklyBusiness(...$request->window('last_week')));
    }

    public function bestSelling(AutomationQueryRequest $request): JsonResponse
    {
        [$from, $to] = $request->window('last_week');

        return $this->success($this->reports->bestSelling($from, $to, (string) ($request->validated('by') ?? 'game'), $request->intOr('limit', 10)));
    }

    public function profitableProducts(AutomationQueryRequest $request): JsonResponse
    {
        [$from, $to] = $request->window('last_week');

        return $this->success($this->reports->profitableProducts($from, $to, $request->intOr('limit', 10)));
    }

    public function underperformingProducts(AutomationQueryRequest $request): JsonResponse
    {
        [$from, $to] = $request->window('last_week');

        return $this->success($this->reports->underperforming($from, $to, $request->intOr('limit', 10)));
    }

    public function supplierPerformance(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->supplierPerformance(...$request->window('last_week')));
    }

    public function gatewayPerformance(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->gatewayPerformance(...$request->window('last_week')));
    }

    public function customerRetention(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->retention(...$request->window('last_week')));
    }

    public function couponPerformance(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->couponPerformance(...$request->window('last_week')));
    }

    public function pricingRecommendations(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->reports->pricingRecommendations($request->thresholdPct((string) config('automation.low_margin_threshold_pct')), $request->intOr('limit', 20)));
    }

    public function expenses(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->financial->expenses(...$request->window('yesterday')));
    }

    public function gatewayFees(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->financial->gatewayFees(...$request->window('yesterday')));
    }

    public function unusualTransactions(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->financial->unusualTransactions(...$request->window('last_hour')));
    }

    public function refunds(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->financial->refunds(...$request->window('last_hour')));
    }

    public function reconciliation(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->financial->reconciliation($request->since(24 * 60)));
    }
}
