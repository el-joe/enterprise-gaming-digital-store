<?php

/**
 * Report, monitor and marketing endpoints: figures come from stored data, missing data is
 * reported as `unavailable` with a reason, and AI output only becomes a pending_review suggestion.
 */

use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Catalog\Models\Product;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use Database\Factories\Currency\CurrencyFactory;

beforeEach(function (): void {
    enableAutomationWorkflows();
    CurrencyFactory::ensure('USD');
    ['token' => $this->token] = automationCredential();
});

it('reports yesterday\'s sales and profit from order_item_financials', function (): void {
    $this->travelTo(now()->setTime(6, 0));
    OrderItemFinancial::factory()->create(['revenue_base' => '30.000000', 'refunded_base' => '0', 'net_profit_base' => '4.500000', 'recognized_at' => now()->subDay()->setTime(12, 0)]);
    OrderItemFinancial::factory()->create(['revenue_base' => '99.000000', 'recognized_at' => now()->subDays(3)]);

    automationGet($this->token, 'reports/sales?period=yesterday', ['workflow' => 'A01'])
        ->assertOk()
        ->assertJsonPath('data.source', 'order_item_financials')
        ->assertJsonPath('data.totals.revenue.amount', '30.00')
        ->assertJsonPath('data.totals.orders_count', 1);

    automationGet($this->token, 'reports/profit?period=yesterday', ['workflow' => 'A02'])
        ->assertOk()
        ->assertJsonPath('data.totals.net_profit.amount', '4.50');

    automationGet($this->token, 'reports/revenue-comparison', ['workflow' => 'A11'])
        ->assertOk()
        ->assertJsonPath('data.current.net_revenue.amount', '30.00')
        ->assertJsonPath('data.previous_period.net_revenue_change_pct', null);
});

it('validates report windows', function (string $query): void {
    automationGet($this->token, "reports/sales?{$query}")->assertUnprocessable()->assertJson(['code' => 'validation_failed']);
})->with(['unknown period' => 'period=forever', 'from without to' => 'from=2026-01-01', 'range too long' => 'from=2024-01-01&to=2026-01-01', 'to before from' => 'from=2026-02-01&to=2026-01-01']);

it('returns unavailable with a reason where the store holds no data', function (string $path, string $workflow, string $pointer): void {
    $response = automationGet($this->token, $path, ['workflow' => $workflow])->assertOk();

    $section = $pointer === '' ? $response->json('data') : data_get($response->json('data'), $pointer);
    expect($section['status'])->toBe('unavailable')
        ->and($section['reason'])->toBeString()->not->toBeEmpty();
})->with([
    'exceptions (F03)' => ['monitor/exceptions', 'F03', ''],
    'database replication (F05)' => ['monitor/database', 'F05', 'replication'],
    'web request latency (F06)' => ['monitor/latency', 'F06', 'unavailable.0'],
    'operating expenses (E01)' => ['reports/expenses', 'E01', 'unavailable.0'],
    'external trends (C01)' => ['data/trending', 'C01', 'unavailable.0'],
    'competitor prices (B10)' => ['reports/pricing-recommendations', 'B10', 'unavailable.0'],
    'guest retention (B08)' => ['reports/customer-retention', 'B08', 'unavailable.0'],
    'provider latency (B07)' => ['reports/gateway-performance', 'B07', 'unavailable.0'],
]);

it('flags failed and stalled orders for A03 and A04', function (): void {
    Order::factory()->create(['status' => OrderStatus::Failed, 'status_changed_at' => now()->subMinutes(5)]);
    Order::factory()->create(['status' => OrderStatus::Failed, 'status_changed_at' => now()->subHours(3)]);
    $stuck = Order::factory()->create(['status' => OrderStatus::PendingVerification, 'status_changed_at' => now()->subHours(2)]);
    Order::factory()->create(['status' => OrderStatus::ManualReview, 'status_changed_at' => now()->subMinutes(5)]);

    automationGet($this->token, 'monitor/orders/failed?since='.urlencode(now()->subMinutes(15)->toIso8601String()), ['workflow' => 'A03'])
        ->assertOk()
        ->assertJsonPath('data.alert', true)
        ->assertJsonPath('data.failed_count', 1);

    automationGet($this->token, 'monitor/orders/pending?older_than=60', ['workflow' => 'A04'])
        ->assertOk()
        ->assertJsonPath('data.alert', true)
        ->assertJsonPath('data.counts_by_status.pending_verification', 1)
        ->assertJsonPath('data.orders.0.order_number', $stuck->order_number)
        ->assertJsonMissingPath('data.counts_by_status.manual_review');
});

it('uses the supplier threshold for warnings and a fraction of it for critical alerts', function (): void {
    Supplier::factory()->create(['status' => SupplierStatus::Active, 'balance_currency' => 'USD', 'low_balance_threshold' => '100.000000', 'last_balance' => '70.000000', 'last_balance_at' => now()]);

    automationGet($this->token, 'monitor/suppliers/balances?level=warning', ['workflow' => 'A05'])
        ->assertOk()
        ->assertJsonPath('data.alert', true)
        ->assertJsonPath('data.below_threshold_count', 1);

    automationGet($this->token, 'monitor/suppliers/balances?level=critical', ['workflow' => 'E03'])
        ->assertOk()
        ->assertJsonPath('data.alert', false)
        ->assertJsonPath('data.suppliers.0.alert_threshold', '50.000000');
});

it('reports no alert and no invented figures on an empty store', function (string $path, string $workflow): void {
    $response = automationGet($this->token, $path, ['workflow' => $workflow])->assertOk();

    expect($response->json('data.alert'))->toBeFalse();
})->with([
    ['monitor/orders/failed', 'A03'],
    ['monitor/orders/pending', 'A04'],
    ['monitor/suppliers/health', 'A06'],
    ['monitor/payments/failures?window=60', 'A07'],
    ['monitor/products/low-margin', 'A08'],
    ['monitor/margin-alerts', 'E05'],
    ['monitor/queues', 'F02'],
    ['monitor/webhooks', 'F08'],
    ['monitor/suppliers/health?detail=1', 'F07'],
]);

it('serves every weekly and financial report on an empty store', function (string $path, string $workflow): void {
    automationGet($this->token, $path, ['workflow' => $workflow])->assertOk()->assertJsonPath('success', true);
})->with([
    ['reports/weekly-business', 'B01'],
    ['reports/best-selling?by=game', 'B02'],
    ['reports/best-selling?by=platform', 'B03'],
    ['reports/profitable-products', 'B04'],
    ['reports/underperforming-products', 'B05'],
    ['reports/supplier-performance', 'B06'],
    ['reports/coupon-performance', 'B09'],
    ['reports/gateway-fees', 'E04'],
    ['reports/unusual-transactions', 'E06'],
    ['reports/refunds', 'E07'],
    ['payments/reconciliation', 'E02'],
]);

it('gives the AI workflows public product context without costs or customers', function (): void {
    $variant = sellableVariant('7', ['manual_cost_base' => '6.123456']);
    $product = $variant->product;
    paidOrderFor($variant, orderAttributes: ['contact_email' => 'someone@example.com']);

    $response = automationGet($this->token, "data/product-context/{$product->public_id}", ['workflow' => 'C09'])
        ->assertOk()
        ->assertJsonPath('data.product_id', $product->public_id)
        ->assertJsonPath('data.sales_last_30_days.units', 1);

    expect($response->getContent())->not->toContain('6.123456')->not->toContain('someone@example.com');

    automationGet($this->token, 'data/product-context/01JAAAAAAAAAAAAAAAAAAAAAAA', ['workflow' => 'C09'])->assertNotFound();
});

it('stores AI output as a pending_review suggestion and publishes nothing', function (): void {
    $product = Product::factory()->create(['description' => null]);
    $body = ['type' => 'seo_description', 'product_id' => $product->public_id, 'content' => ['title' => 'Steam card', 'body' => 'Draft description', 'rationale' => 'Missing description']];

    automationPost($this->token, 'suggestions', $body, ['workflow' => 'C08', 'key' => 'C08:2026-11-11'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending_review')
        ->assertJsonPath('data.created', true);
    automationPost($this->token, 'suggestions', $body, ['workflow' => 'C08', 'key' => 'C08:2026-11-11'])
        ->assertOk()
        ->assertJsonPath('data.created', false);

    $suggestion = AiSuggestion::query()->sole();
    expect($suggestion->status)->toBe(AiSuggestionStatus::PendingReview)
        ->and($suggestion->subject_id)->toBe($product->id)
        ->and($suggestion->content['generated_by'])->toBe('n8n')
        ->and($suggestion->reviewed_by)->toBeNull()
        ->and($product->refresh()->description)->toBeNull();
});

it('lets each workflow create only its own suggestion types', function (): void {
    automationPost($this->token, 'suggestions', ['type' => 'coupon', 'content' => ['title' => 'x']], ['workflow' => 'C07'])
        ->assertForbidden();
    automationPost($this->token, 'suggestions', ['type' => 'pricing', 'content' => ['title' => 'x']], ['workflow' => 'A01'])
        ->assertForbidden();
    automationPost($this->token, 'suggestions', ['type' => 'blog_idea', 'content' => []], ['workflow' => 'C07'])
        ->assertUnprocessable();

    expect(AiSuggestion::query()->count())->toBe(0);
});
