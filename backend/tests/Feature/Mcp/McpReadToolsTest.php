<?php

/**
 * Every MCP read tool against real fixture data, PII masking, `unavailable` results and the
 * query budget of the list tools.
 */

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use App\Domains\Suppliers\Enums\SupplierSyncRunStatus;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use App\Domains\Suppliers\Models\SupplierSyncRun;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    ['token' => $this->token] = mcpToken();
});

/**
 * A paid order with one delivered unit for a customer with identifiable contact details.
 */
function mcpCustomerOrder(array $orderAttributes = [], OrderStatus $status = OrderStatus::Completed): Order
{
    $variant = sellableVariant('8', ['sku' => 'STEAM-USD-'.random_int(1000, 9999)]);

    return paidOrderFor($variant, orderAttributes: [
        'contact_email' => 'jane.doe@example.com',
        'contact_phone' => '+20 123 456 7890',
        'customer_name' => 'Jane Doe',
        'ip_address' => '203.0.113.77',
        ...$orderAttributes,
    ], status: $status);
}

it('lists, finds and searches products with variant prices as decimal strings', function (): void {
    $variant = sellableVariant('9', ['sku' => 'PSN-US-25', 'price_base' => '27.500000', 'title' => 'PSN 25']);
    $product = $variant->product;
    $product->forceFill(['sku' => 'PSN-US', 'name' => 'PlayStation Store Card'])->save();
    Product::factory()->create(['status' => ProductStatus::Draft, 'name' => 'Draft only']);

    mcpGet($this->token, 'products', ['status' => 'active'])
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.sku', 'PSN-US')
        ->assertJsonPath('data.0.active_variants_count', 1);

    mcpGet($this->token, 'products/PSN-US')
        ->assertOk()
        ->assertJsonPath('data.id', $product->public_id)
        ->assertJsonPath('data.variants.0.sku', 'PSN-US-25')
        ->assertJsonPath('data.variants.0.price_base', ['amount' => '27.50', 'currency' => 'USD']);

    mcpGet($this->token, "products/{$product->public_id}")->assertOk()->assertJsonPath('data.sku', 'PSN-US');
    mcpGet($this->token, 'products/search', ['query' => 'PlayStation'])->assertOk()->assertJsonPath('meta.total', 1);
    mcpGet($this->token, 'products/search', ['query' => 'PSN-US-25'])->assertOk()->assertJsonPath('data.0.sku', 'PSN-US');
    mcpGet($this->token, 'products/search', ['query' => '%'])->assertUnprocessable();
    mcpGet($this->token, 'products/NOPE')->assertNotFound()->assertJson(['code' => 'not_found']);
});

it('prices a variant with the pricing engine and reports availability and stock counts only', function (): void {
    $variant = sellableVariant('8', ['sku' => 'XBOX-10']);
    mapToSupplier($variant, stockSupplier(['name' => 'Internal stock']), '8');
    DigitalCode::factory()->count(2)->create(['product_variant_id' => $variant->id, 'stock_status' => DigitalCodeStockStatus::Available]);

    $pricing = mcpGet($this->token, 'variants/XBOX-10/pricing')->assertOk();
    expect($pricing->json('data.purchasable'))->toBeTrue()
        ->and($pricing->json('data.price.currency'))->toBe('USD')
        ->and($pricing->json('data.price.amount'))->toBeString()
        ->and($pricing->json('data.cost_base'))->toBe(['amount' => '8.00', 'currency' => 'USD']);

    mcpGet($this->token, 'variants/XBOX-10/availability')
        ->assertOk()
        ->assertJsonPath('data.sellable', true)
        ->assertJsonPath('data.internal_stock_available', 2)
        ->assertJsonPath('data.suppliers.0.supplier', 'Internal stock')
        ->assertJsonMissingPath('data.suppliers.0.credentials');

    mcpGet($this->token, 'variants/XBOX-10/pricing', ['currency' => 'ZZZ'])->assertUnprocessable();
});

it('masks customer identifiers in order lists, details and searches', function (): void {
    $order = mcpCustomerOrder();

    $list = mcpGet($this->token, 'orders')->assertOk()->assertJsonPath('data.0.customer.email', 'j***@example.com');
    $details = mcpGet($this->token, "orders/{$order->order_number}")
        ->assertOk()
        ->assertJsonPath('data.customer.email', 'j***@example.com')
        ->assertJsonPath('data.customer.phone', '***90')
        ->assertJsonPath('data.customer.name', 'J***')
        ->assertJsonPath('data.items.0.units.0.status', 'pending');
    $search = mcpGet($this->token, 'orders/search', ['email' => 'JANE.DOE@example.com'])->assertOk()->assertJsonPath('meta.total', 1);

    foreach ([$list, $details, $search] as $response) {
        expect($response->getContent())
            ->not->toContain('jane.doe')
            ->not->toContain('Jane Doe')
            ->not->toContain('456 7890')
            ->not->toContain('203.0.113.77');
    }

    mcpGet($this->token, "orders/{$order->order_number}/status")->assertOk()->assertJson(['data' => ['status' => 'completed', 'is_final' => true, 'paid' => true]]);
});

it('searches customer orders by exact identifiers only', function (): void {
    $order = mcpCustomerOrder();

    mcpGet($this->token, 'orders/search', ['email' => 'jane.doe@example'])->assertOk()->assertJsonPath('meta.total', 0);
    mcpGet($this->token, 'orders/search', ['email' => 'jane'])->assertUnprocessable();
    mcpGet($this->token, 'orders/search')->assertUnprocessable();
    mcpGet($this->token, 'orders/search', ['email' => 'jane.doe@example.com', 'order_number' => $order->order_number])->assertUnprocessable();
    mcpGet($this->token, 'orders/search', ['order_number' => $order->order_number])->assertOk()->assertJsonPath('data.0.order_number', $order->order_number);
});

it('summarises sales and profit from order_item_financials within the window only', function (): void {
    $order = mcpCustomerOrder();
    $item = $order->orderItems()->sole();
    $financial = ['order_id' => $order->id, 'order_item_id' => $item->id, 'product_id' => $item->product_id, 'revenue_base' => '20.000000', 'refunded_base' => '5.000000', 'supplier_cost_base' => '9.000000', 'gateway_fee_base' => '0.900000', 'fx_cost_base' => '0', 'other_cost_base' => '0', 'gross_profit_base' => '11.000000', 'net_profit_base' => '5.100000', 'gateway_fee_is_estimate' => true, 'recognized_at' => now()->subDay()];
    OrderItemFinancial::factory()->create($financial);
    $old = mcpCustomerOrder();
    OrderItemFinancial::factory()->create([...$financial, 'order_id' => $old->id, 'order_item_id' => $old->orderItems()->sole()->id, 'recognized_at' => now()->subDays(40)]);

    $range = ['from' => now()->subDays(7)->toDateString(), 'to' => now()->toDateString()];

    mcpGet($this->token, 'analytics/sales-summary', [...$range, 'group_by' => 'none'])
        ->assertOk()
        ->assertJsonPath('data.source', 'order_item_financials')
        ->assertJsonPath('data.totals.orders_count', 1)
        ->assertJsonPath('data.totals.units_count', 1)
        ->assertJsonPath('data.totals.revenue', ['amount' => '20.00', 'currency' => 'USD'])
        ->assertJsonPath('data.totals.net_revenue', ['amount' => '15.00', 'currency' => 'USD'])
        ->assertJsonPath('data.periods.0.period', 'all');

    mcpGet($this->token, 'analytics/profit-summary', $range)
        ->assertOk()
        ->assertJsonPath('data.totals.net_profit', ['amount' => '5.10', 'currency' => 'USD'])
        ->assertJsonPath('data.totals.net_margin_pct', '34.00')
        ->assertJsonPath('data.estimated_fee_rows', 1)
        ->assertJsonPath('data.periods.0.period', now()->subDay()->toDateString());

    mcpGet($this->token, 'analytics/top-selling-products', $range)
        ->assertOk()
        ->assertJsonCount(1, 'data.products')
        ->assertJsonPath('data.products.0.revenue', ['amount' => '20.00', 'currency' => 'USD']);

    mcpGet($this->token, 'analytics/sales-summary', ['from' => now()->subDays(400)->toDateString(), 'to' => now()->toDateString()])->assertUnprocessable();
    mcpGet($this->token, 'analytics/sales-summary', ['from' => now()->toDateString(), 'to' => now()->subDay()->toDateString()])->assertUnprocessable();
});

it('reports conversion as unavailable where GA4 data does not exist, without inventing numbers', function (): void {
    mcpCustomerOrder();
    Order::factory()->create();

    $response = mcpGet($this->token, 'analytics/conversion-metrics', ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString()])->assertOk();

    expect($response->json('data.status'))->toBe('partial')
        ->and($response->json('data.unavailable.0'))->toMatchArray(['metric' => 'visitor_conversion_rate', 'status' => 'unavailable'])
        ->and($response->json('data.unavailable.0.reason'))->toContain('M18')
        ->and($response->json('data.checkout_to_payment'))->toMatchArray(['orders_placed' => 2, 'orders_paid' => 1, 'payment_rate_pct' => '50.00']);
});

it('reports supplier performance, balances, status and sync runs without credentials', function (): void {
    $supplier = reloadlySupplier(['name' => 'Reloadly main', 'low_balance_threshold' => '2000']);
    $unit = OrderItemUnit::query()->where('order_id', mcpCustomerOrder()->id)->sole();
    SupplierTransaction::factory()->create(['order_item_unit_id' => $unit->id, 'supplier_id' => $supplier->id, 'status' => SupplierTransactionStatus::Rejected, 'attempt_no' => 1]);
    SupplierTransaction::factory()->create(['order_item_unit_id' => $unit->id, 'supplier_id' => $supplier->id, 'status' => SupplierTransactionStatus::Succeeded, 'attempt_no' => 2, 'submitted_at' => now()->subSeconds(30), 'completed_at' => now()]);
    SupplierHealthCheck::factory()->create(['supplier_id' => $supplier->id, 'reachable' => true, 'latency_ms' => 120]);
    SupplierSyncRun::factory()->create(['supplier_id' => $supplier->id, 'status' => SupplierSyncRunStatus::Failed, 'error' => 'provider said: secret-body', 'finished_at' => now()]);

    $range = ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString()];

    mcpGet($this->token, 'analytics/supplier-performance', $range)
        ->assertOk()
        ->assertJsonPath('data.suppliers.0.attempts', 2)
        ->assertJsonPath('data.suppliers.0.success_rate_pct', '50.00')
        ->assertJsonPath('data.suppliers.0.avg_seconds_to_success', 30);

    mcpGet($this->token, 'suppliers/balances')
        ->assertOk()
        ->assertJsonPath('data.suppliers.0.balance', ['amount' => '1000.00', 'currency' => 'USD'])
        ->assertJsonPath('data.suppliers.0.is_low', true);

    mcpGet($this->token, 'suppliers/status', ['supplier' => 'Reloadly main'])->assertOk()->assertJsonPath('data.suppliers.0.last_health_check.reachable', true);
    mcpGet($this->token, 'suppliers/status', ['supplier' => 'nobody'])->assertNotFound();

    $sync = mcpGet($this->token, 'suppliers/sync-status')->assertOk()->assertJsonPath('data.suppliers.0.catalog.has_error', true);

    $all = $sync->getContent().mcpGet($this->token, 'suppliers/balances')->getContent().mcpGet($this->token, 'suppliers/status')->getContent();
    expect($all)->not->toContain('test-client-secret-value')->not->toContain('test-client-id')->not->toContain('secret-body');
});

it('ranks best sellers and underperformers and reports coupon performance and trends as aggregates', function (): void {
    $order = mcpCustomerOrder();
    $idle = sellableVariant('5')->product;
    $coupon = Coupon::factory()->create(['code' => 'SPRING10']);
    CouponRedemption::factory()->create(['coupon_id' => $coupon->id, 'order_id' => $order->id, 'discount_base' => '2.000000', 'status' => CouponRedemptionStatus::Redeemed]);

    $range = ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString()];

    mcpGet($this->token, 'marketing/best-selling-products', $range)->assertOk()->assertJsonPath('data.products.0.units_count', 1);
    mcpGet($this->token, 'marketing/underperforming-products', $range)->assertOk()->assertJsonPath('data.products.0.sku', $idle->sku)->assertJsonPath('data.products.0.units_count', 0);
    mcpGet($this->token, 'marketing/coupon-performance', [...$range, 'code' => 'spring10'])
        ->assertOk()
        ->assertJsonPath('data.coupons.0.code', 'SPRING10')
        ->assertJsonPath('data.coupons.0.discount', ['amount' => '2.00', 'currency' => 'USD']);

    $trends = mcpGet($this->token, 'marketing/customer-purchase-trends', [...$range, 'group_by' => 'month'])
        ->assertOk()
        ->assertJsonPath('data.periods.0.orders_count', 1)
        ->assertJsonPath('data.top_countries.0.country_code', 'US');

    expect($trends->getContent())->not->toContain('example.com');
});

it('lists stalled orders, failed payments, open reconciliation and system health', function (): void {
    $stalled = mcpCustomerOrder(['status_changed_at' => now()->subHours(2)], OrderStatus::Processing);
    mcpCustomerOrder([], OrderStatus::Processing);
    Payment::factory()->create(['status' => PaymentStatus::Failed, 'failure_code' => 'card_declined', 'failure_message' => 'Provider says secret-detail']);
    ReconciliationItem::factory()->create(['details' => ['provider_body' => 'raw-provider-payload']]);

    mcpGet($this->token, 'operations/pending-orders', ['older_than_minutes' => 60])
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.order_number', $stalled->order_number);

    $failed = mcpGet($this->token, 'operations/failed-transactions', ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString()])
        ->assertOk()
        ->assertJsonPath('data.0.failure_code', 'card_declined');
    $reconciliation = mcpGet($this->token, 'operations/pending-reconciliation')->assertOk()->assertJsonPath('meta.total', 1);

    expect($failed->getContent().$reconciliation->getContent())->not->toContain('secret-detail')->not->toContain('raw-provider-payload');

    mcpGet($this->token, 'operations/system-health')
        ->assertOk()
        ->assertJsonPath('data.checks.database.status', 'ok')
        ->assertJsonPath('data.checks.scheduler.status', 'degraded')
        ->assertJsonPath('data.checks.scheduler.source', 'scheduled_task_runs')
        ->assertJsonPath('data.checks.fulfilment.open_manual_tasks', 0);
});

it('lists failed orders in the window', function (): void {
    $failed = mcpCustomerOrder([], OrderStatus::Failed);
    mcpCustomerOrder();

    mcpGet($this->token, 'analytics/failed-orders', ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString()])
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.order_number', $failed->order_number);
});

it('finds low-margin variants and counts those with unknown cost', function (): void {
    foreach (['THIN-10' => '9.50', 'FAT-10' => '5', 'NOCOST' => null] as $sku => $cost) {
        $variant = sellableVariant('1', ['sku' => $sku, 'price_base' => '10.000000', 'manual_cost_base' => $cost]);
        VariantPrice::factory()->create(['product_variant_id' => $variant->id, 'country_code' => '*', 'currency' => 'USD', 'price' => '10.000000']);
    }

    $response = mcpGet($this->token, 'inventory/low-margin-products', ['threshold_pct' => '15'])->assertOk();

    expect(collect($response->json('data'))->pluck('variant_sku')->all())->toBe(['THIN-10'])
        ->and($response->json('meta.variants_cost_unknown'))->toBe(1)
        ->and($response->json('meta.variants_scanned'))->toBe(3);
});

it('runs a constant number of queries for order and product lists', function (): void {
    $count = static function (string $token, string $path): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        mcpGet($token, $path)->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queries;
    };

    mcpCustomerOrder();
    mcpCustomerOrder();
    mcpGet($this->token, 'products')->assertOk();
    $ordersFew = $count($this->token, 'orders');
    $productsFew = $count($this->token, 'products');

    foreach (range(1, 4) as $ignored) {
        mcpCustomerOrder();
    }

    expect($count($this->token, 'orders'))->toBe($ordersFew)
        ->and($count($this->token, 'products'))->toBe($productsFew);
});

it('never lists a delivered unit code or its mask in order details', function (): void {
    $order = mcpCustomerOrder();
    $unit = OrderItemUnit::query()->where('order_id', $order->id)->sole();
    $unit->forceFill(['status' => OrderItemUnitStatus::Delivered, 'delivered_at' => now()])->save();
    DigitalCode::factory()->create(['order_item_unit_id' => $unit->id, 'masked_code' => '****9911']);

    expect(mcpGet($this->token, "orders/{$order->order_number}")->assertOk()->getContent())->not->toContain('9911');
});
