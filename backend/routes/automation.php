<?php

use App\Http\Controllers\Automation\CustomerMessagingController;
use App\Http\Controllers\Automation\ExecutionController;
use App\Http\Controllers\Automation\JobController;
use App\Http\Controllers\Automation\MarketingController;
use App\Http\Controllers\Automation\MonitorController;
use App\Http\Controllers\Automation\ReportController;
use Illuminate\Support\Facades\Route;

/**
 * Automation API for n8n (M17, docs/N8N_AUTOMATIONS.md). Prefix /api/v1/automation, middleware
 * group `automation`: automation_logs call row, bearer token of an `n8n` service account plus
 * HMAC signature, timestamp window and nonce, enabled workflow, per-account rate limit. Route
 * names after `automation.` are recorded as the endpoint.
 *
 * n8n never changes transactional state through this API: it reads reports, logs executions,
 * queues tasks it drives (the same commands the scheduler runs), stores review-only suggestions
 * and logs messages it sent.
 */
Route::middleware('automation.scope:executions:write')->group(static function (): void {
    Route::post('executions', [ExecutionController::class, 'store'])->name('executions');
    Route::post('alerts', [ExecutionController::class, 'alert'])->name('alerts');
});

Route::middleware('automation.scope:reports:read')->group(static function (): void {
    Route::prefix('reports')->name('reports.')->group(static function (): void {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('revenue-comparison', [ReportController::class, 'revenueComparison'])->name('revenue_comparison');
        Route::get('weekly-business', [ReportController::class, 'weeklyBusiness'])->name('weekly_business');
        Route::get('best-selling', [ReportController::class, 'bestSelling'])->name('best_selling');
        Route::get('profitable-products', [ReportController::class, 'profitableProducts'])->name('profitable_products');
        Route::get('underperforming-products', [ReportController::class, 'underperformingProducts'])->name('underperforming_products');
        Route::get('supplier-performance', [ReportController::class, 'supplierPerformance'])->name('supplier_performance');
        Route::get('gateway-performance', [ReportController::class, 'gatewayPerformance'])->name('gateway_performance');
        Route::get('customer-retention', [ReportController::class, 'customerRetention'])->name('customer_retention');
        Route::get('coupon-performance', [ReportController::class, 'couponPerformance'])->name('coupon_performance');
        Route::get('pricing-recommendations', [ReportController::class, 'pricingRecommendations'])->name('pricing_recommendations');
        Route::get('expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('gateway-fees', [ReportController::class, 'gatewayFees'])->name('gateway_fees');
        Route::get('unusual-transactions', [ReportController::class, 'unusualTransactions'])->name('unusual_transactions');
        Route::get('refunds', [ReportController::class, 'refunds'])->name('refunds');
    });
    Route::get('payments/reconciliation', [ReportController::class, 'reconciliation'])->name('payments.reconciliation');
});

Route::middleware('automation.scope:monitor:read')->prefix('monitor')->name('monitor.')->group(static function (): void {
    Route::get('orders/failed', [MonitorController::class, 'failedOrders'])->name('orders_failed');
    Route::get('orders/pending', [MonitorController::class, 'pendingOrders'])->name('orders_pending');
    Route::get('suppliers/balances', [MonitorController::class, 'supplierBalances'])->name('supplier_balances');
    Route::get('suppliers/health', [MonitorController::class, 'supplierHealth'])->name('supplier_health');
    Route::get('payments/failures', [MonitorController::class, 'paymentFailures'])->name('payment_failures');
    Route::get('products/low-margin', [MonitorController::class, 'lowMarginProducts'])->name('low_margin_products');
    Route::get('margin-alerts', [MonitorController::class, 'marginAlerts'])->name('margin_alerts');
    Route::get('queues', [MonitorController::class, 'queues'])->name('queues');
    Route::get('exceptions', [MonitorController::class, 'exceptions'])->name('exceptions');
    Route::get('scheduler', [MonitorController::class, 'scheduler'])->name('scheduler');
    Route::get('database', [MonitorController::class, 'database'])->name('database');
    Route::get('latency', [MonitorController::class, 'latency'])->name('latency');
    Route::get('webhooks', [MonitorController::class, 'webhooks'])->name('webhooks');
});

Route::middleware('automation.scope:jobs:run')->prefix('jobs')->name('jobs.')->group(static function (): void {
    Route::post('catalog-sync', [JobController::class, 'catalogSync'])->name('catalog_sync');
    Route::post('exchange-rates', [JobController::class, 'exchangeRates'])->name('exchange_rates');
    Route::post('reconcile-payments', [JobController::class, 'reconcilePayments'])->name('reconcile_payments');
});

Route::middleware('automation.scope:marketing:read')->prefix('data')->name('data.')->group(static function (): void {
    Route::get('trending', [MarketingController::class, 'trending'])->name('trending');
    Route::get('product-context/{productId}', [MarketingController::class, 'productContext'])->whereUlid('productId')->name('product_context');
    Route::get('products/missing-descriptions', [MarketingController::class, 'missingDescriptions'])->name('missing_descriptions');
});

Route::post('suggestions', [MarketingController::class, 'storeSuggestion'])->middleware('automation.scope:suggestions:write')->name('suggestions');

Route::middleware('automation.scope:customers:message')->prefix('customers')->name('customers.')->group(static function (): void {
    Route::post('messages/prepare', [CustomerMessagingController::class, 'prepare'])->name('messages_prepare');
    Route::post('messages/log', [CustomerMessagingController::class, 'log'])->name('messages_log');
    Route::get('segments/{segment}', [CustomerMessagingController::class, 'segment'])->whereIn('segment', ['abandoned', 'review-eligible', 'reactivation', 'recommendations'])->name('segments');
});
