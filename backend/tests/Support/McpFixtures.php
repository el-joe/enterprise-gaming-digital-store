<?php

/**
 * Fixtures for the MCP API tests (M16).
 */

use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpScope;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * An MCP service account and a token with the given scopes. Read-only unless `$writable`.
 *
 * @param  list<McpScope|string>|null  $scopes  null means all read scopes; strings are raw abilities
 * @param  array<string, mixed>  $account
 * @return array{account: ServiceAccount, token: string, model: PersonalAccessToken}
 */
function mcpToken(?array $scopes = null, bool $writable = false, array $account = [], ?DateTimeInterface $expiresAt = null): array
{
    $serviceAccount = ServiceAccount::factory()->create(['kind' => ServiceAccountKind::Mcp, 'is_read_only' => ! $writable, ...$account]);

    $abilities = array_map(
        static fn (McpScope|string $scope): string => $scope instanceof McpScope ? $scope->ability() : $scope,
        $scopes ?? McpScope::readScopes(),
    );

    $issued = $serviceAccount->createToken('mcp-test', $abilities, $expiresAt);

    return ['account' => $serviceAccount, 'token' => $issued->plainTextToken, 'model' => $issued->accessToken];
}

/**
 * A writable account whose token holds every scope, including `actions:confirm`.
 *
 * @return array{account: ServiceAccount, token: string, model: PersonalAccessToken}
 */
function mcpWriter(bool $canConfirm = true): array
{
    return mcpToken([...McpScope::readScopes(), ...McpScope::writeScopes(), ...($canConfirm ? [McpScope::ActionsConfirm] : [])], writable: true);
}

/**
 * @param  array<string, mixed>  $query
 */
function mcpGet(string $token, string $path, array $query = []): TestResponse
{
    return test()->withToken($token)->getJson('/api/v1/mcp/'.ltrim($path, '/').($query === [] ? '' : '?'.http_build_query($query)));
}

/**
 * @param  array<string, mixed>  $body
 */
function mcpPost(string $token, string $path, array $body = []): TestResponse
{
    return test()->withToken($token)->postJson('/api/v1/mcp/'.ltrim($path, '/'), $body);
}

/**
 * Every read tool with a valid request, its route and its scope.
 *
 * @return array<string, array{0: string, 1: array<string, mixed>, 2: McpScope}>
 */
function mcpReadTools(): array
{
    $range = ['from' => now()->subDays(7)->toDateString(), 'to' => now()->toDateString()];

    return [
        'list_products' => ['products', [], McpScope::ProductsRead],
        'get_product' => ['products/NO-SUCH-SKU', [], McpScope::ProductsRead],
        'search_products' => ['products/search', ['query' => 'gift'], McpScope::ProductsRead],
        'get_product_pricing' => ['variants/NO-SUCH-SKU/pricing', [], McpScope::ProductsRead],
        'check_product_availability' => ['variants/NO-SUCH-SKU/availability', [], McpScope::ProductsRead],
        'list_orders' => ['orders', [], McpScope::OrdersRead],
        'get_order_details' => ['orders/NOSUCHORDER', [], McpScope::OrdersRead],
        'get_order_status' => ['orders/NOSUCHORDER/status', [], McpScope::OrdersRead],
        'search_customer_orders' => ['orders/search', ['order_number' => 'NOSUCHORDER'], McpScope::OrdersRead],
        'get_sales_summary' => ['analytics/sales-summary', $range, McpScope::AnalyticsRead],
        'get_profit_summary' => ['analytics/profit-summary', $range, McpScope::AnalyticsRead],
        'get_top_selling_products' => ['analytics/top-selling-products', $range, McpScope::AnalyticsRead],
        'get_supplier_performance' => ['analytics/supplier-performance', $range, McpScope::AnalyticsRead],
        'get_failed_orders' => ['analytics/failed-orders', $range, McpScope::AnalyticsRead],
        'get_conversion_metrics' => ['analytics/conversion-metrics', $range, McpScope::AnalyticsRead],
        'get_supplier_balances' => ['suppliers/balances', [], McpScope::SuppliersRead],
        'get_supplier_status' => ['suppliers/status', [], McpScope::SuppliersRead],
        'get_supplier_sync_status' => ['suppliers/sync-status', [], McpScope::SuppliersRead],
        'get_low_margin_products' => ['inventory/low-margin-products', [], McpScope::SuppliersRead],
        'get_best_selling_products' => ['marketing/best-selling-products', $range, McpScope::MarketingRead],
        'get_underperforming_products' => ['marketing/underperforming-products', $range, McpScope::MarketingRead],
        'get_coupon_performance' => ['marketing/coupon-performance', $range, McpScope::MarketingRead],
        'get_customer_purchase_trends' => ['marketing/customer-purchase-trends', $range, McpScope::MarketingRead],
        'get_pending_orders' => ['operations/pending-orders', [], McpScope::OperationsRead],
        'get_failed_transactions' => ['operations/failed-transactions', $range, McpScope::OperationsRead],
        'get_pending_reconciliation' => ['operations/pending-reconciliation', [], McpScope::OperationsRead],
        'get_system_health' => ['operations/system-health', [], McpScope::OperationsRead],
    ];
}
