<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\Services\McpAnalyticsReader;
use App\Http\Requests\Mcp\AnalyticsRangeRequest;
use Illuminate\Http\JsonResponse;

/**
 * Analytics and marketing aggregates. Figures are computed from stored data; anything the store
 * does not collect yet is reported as `unavailable` with a reason.
 */
class AnalyticsController extends McpController
{
    public function __construct(private McpAnalyticsReader $reader) {}

    public function sales(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->salesSummary($request->from(), $request->to(), $request->groupBy()));
    }

    public function profit(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->profitSummary($request->from(), $request->to(), $request->groupBy()));
    }

    public function topProducts(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->topSellingProducts($request->from(), $request->to(), $request->limit()));
    }

    public function supplierPerformance(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->supplierPerformance($request->from(), $request->to()));
    }

    public function conversion(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->conversionMetrics($request->from(), $request->to()));
    }

    public function bestSellers(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->bestSellingProducts($request->from(), $request->to(), $request->limit()));
    }

    public function underperforming(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->underperformingProducts($request->from(), $request->to(), $request->limit()));
    }

    public function coupons(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->couponPerformance($request->from(), $request->to(), $request->validated('code')));
    }

    public function purchaseTrends(AnalyticsRangeRequest $request): JsonResponse
    {
        return $this->success($this->reader->customerPurchaseTrends($request->from(), $request->to(), $request->groupBy()));
    }
}
