<?php

use App\Http\Controllers\Mcp\ActionController;
use App\Http\Controllers\Mcp\AnalyticsController;
use App\Http\Controllers\Mcp\OperationsController;
use App\Http\Controllers\Mcp\OrderController;
use App\Http\Controllers\Mcp\ProductController;
use App\Http\Controllers\Mcp\SessionController;
use App\Http\Middleware\Mcp\RequireMcpWriteAccess;
use Illuminate\Support\Facades\Route;

/**
 * MCP API (M16, docs/MCP_SERVER.md). Prefix /api/v1/mcp, middleware group `mcp`: audit row,
 * bearer token of an `mcp` service account, per-account rate limit. One route per tool; the route
 * name after `mcp.` is the tool name recorded in mcp_audit_logs.
 *
 * Deliberately absent: refunds, digital codes, payment or supplier credentials, settings,
 * customer records, and any query or SQL endpoint.
 */
// Not a model tool: the MCP server reads its own account, scopes and read-only flag at start-up
// to decide which tools to register.
Route::get('session', [SessionController::class, 'show'])->name('session');

Route::middleware('mcp.scope:products:read')->group(static function (): void {
    Route::get('products', [ProductController::class, 'index'])->name('list_products');
    Route::get('products/search', [ProductController::class, 'search'])->name('search_products');
    Route::get('products/{idOrSku}', [ProductController::class, 'show'])->where('idOrSku', '[A-Za-z0-9_.-]{1,80}')->name('get_product');
    Route::get('variants/{variantSku}/pricing', [ProductController::class, 'pricing'])->where('variantSku', '[A-Za-z0-9_.-]{1,80}')->name('get_product_pricing');
    Route::get('variants/{variantSku}/availability', [ProductController::class, 'availability'])->where('variantSku', '[A-Za-z0-9_.-]{1,80}')->name('check_product_availability');
});

Route::middleware('mcp.scope:orders:read')->group(static function (): void {
    Route::get('orders', [OrderController::class, 'index'])->name('list_orders');
    Route::get('orders/search', [OrderController::class, 'search'])->name('search_customer_orders');
    Route::get('orders/{orderNumber}', [OrderController::class, 'show'])->where('orderNumber', '[A-Za-z0-9-]{1,24}')->name('get_order_details');
    Route::get('orders/{orderNumber}/status', [OrderController::class, 'status'])->where('orderNumber', '[A-Za-z0-9-]{1,24}')->name('get_order_status');
});

Route::middleware('mcp.scope:analytics:read')->prefix('analytics')->group(static function (): void {
    Route::get('sales-summary', [AnalyticsController::class, 'sales'])->name('get_sales_summary');
    Route::get('profit-summary', [AnalyticsController::class, 'profit'])->name('get_profit_summary');
    Route::get('top-selling-products', [AnalyticsController::class, 'topProducts'])->name('get_top_selling_products');
    Route::get('supplier-performance', [AnalyticsController::class, 'supplierPerformance'])->name('get_supplier_performance');
    Route::get('failed-orders', [OrderController::class, 'failed'])->name('get_failed_orders');
    Route::get('conversion-metrics', [AnalyticsController::class, 'conversion'])->name('get_conversion_metrics');
});

Route::middleware('mcp.scope:suppliers:read')->group(static function (): void {
    Route::get('suppliers/balances', [OperationsController::class, 'supplierBalances'])->name('get_supplier_balances');
    Route::get('suppliers/status', [OperationsController::class, 'supplierStatus'])->name('get_supplier_status');
    Route::get('suppliers/sync-status', [OperationsController::class, 'supplierSyncStatus'])->name('get_supplier_sync_status');
    Route::get('inventory/low-margin-products', [ProductController::class, 'lowMargin'])->name('get_low_margin_products');
});

Route::middleware('mcp.scope:marketing:read')->prefix('marketing')->group(static function (): void {
    Route::get('best-selling-products', [AnalyticsController::class, 'bestSellers'])->name('get_best_selling_products');
    Route::get('underperforming-products', [AnalyticsController::class, 'underperforming'])->name('get_underperforming_products');
    Route::get('coupon-performance', [AnalyticsController::class, 'coupons'])->name('get_coupon_performance');
    Route::get('customer-purchase-trends', [AnalyticsController::class, 'purchaseTrends'])->name('get_customer_purchase_trends');
});

Route::middleware('mcp.scope:operations:read')->prefix('operations')->group(static function (): void {
    Route::get('pending-orders', [OrderController::class, 'pending'])->name('get_pending_orders');
    Route::get('failed-transactions', [OperationsController::class, 'failedTransactions'])->name('get_failed_transactions');
    Route::get('pending-reconciliation', [OperationsController::class, 'pendingReconciliation'])->name('get_pending_reconciliation');
    Route::get('system-health', [OperationsController::class, 'systemHealth'])->name('get_system_health');
});

// Write tools: RequireMcpWriteAccess checks the tool's scope and the read-only flag before validation;
// McpPendingActionService checks them again when storing and when executing.
Route::prefix('actions')->group(static function (): void {
    Route::middleware(RequireMcpWriteAccess::class)->group(static function (): void {
        Route::post('update-product-price', [ActionController::class, 'request'])->name('update_product_price');
        Route::post('activate-product', [ActionController::class, 'request'])->name('activate_product');
        Route::post('deactivate-product', [ActionController::class, 'request'])->name('deactivate_product');
        Route::post('create-coupon', [ActionController::class, 'request'])->name('create_coupon');
        Route::post('trigger-catalog-sync', [ActionController::class, 'request'])->name('trigger_catalog_sync');
        Route::post('retry-verified-failed-order', [ActionController::class, 'request'])->name('retry_verified_failed_order');
    });
    Route::post('{actionId}/confirm', [ActionController::class, 'confirm'])->whereUlid('actionId')->name('confirm_action');
    Route::get('{actionId}', [ActionController::class, 'show'])->whereUlid('actionId')->name('get_action_status');
});
