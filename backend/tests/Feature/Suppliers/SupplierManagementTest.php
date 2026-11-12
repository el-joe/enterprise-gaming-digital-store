<?php

/**
 * Supplier admin API, catalog and price sync, balances, health, reconciliation, and the
 * inherited pricing items. Reloadly mocked; awaiting sandbox verification.
 */

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Enums\ReconciliationItemType;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Suppliers\Enums\SupplierHealth;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierCredential;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use App\Domains\Suppliers\Services\SupplierCatalogSync;
use App\Domains\Suppliers\Services\SupplierMonitor;
use App\Domains\Suppliers\Services\SupplierPriceSync;
use App\Domains\Suppliers\Services\SupplierPriceWriter;
use App\Domains\Suppliers\Services\SupplierReconciler;
use Carbon\CarbonImmutable;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

function catalogBody(string $map = '{"25.0":24.5,"50":49}'): string
{
    return '{"content":[{"productId":1234,"productName":"Steam Wallet","denominationType":"FIXED","recipientCurrencyCode":"USD","senderCurrencyCode":"USD","senderFee":0.5,"senderFeePercentage":0,"discountPercentage":3,"fixedRecipientDenominations":[25,50],"fixedRecipientToSenderDenominationsMap":'.$map.',"status":"ACTIVE"}],"last":true}';
}

describe('admin API', function (): void {
    it('requires supplier permissions', function (): void {
        $supplier = reloadlySupplier();

        $this->getJson('/admin/api/suppliers')->assertUnauthorized();
        actingAsStaffWith([]);
        $this->getJson('/admin/api/suppliers')->assertForbidden();
        actingAsStaffWith(['suppliers.view']);
        $this->getJson('/admin/api/suppliers')->assertOk()->assertJsonPath('data.0.id', $supplier->id);
        $this->postJson('/admin/api/suppliers', ['name' => 'X', 'adapter_key' => 'internal_stock'])->assertForbidden();
        $this->postJson("/admin/api/suppliers/{$supplier->id}/credentials", ['mode' => 'sandbox', 'credentials' => ['client_id' => 'a']])->assertForbidden();
    });

    it('creates a supplier inactive by default and refuses an unknown adapter or an unsafe base URL', function (): void {
        actingAsStaffWith(['suppliers.manage']);

        $this->postJson('/admin/api/suppliers', ['name' => 'Stock B', 'adapter_key' => 'internal_stock'])
            ->assertCreated()->assertJsonPath('data.status', 'inactive');
        $this->postJson('/admin/api/suppliers', ['name' => 'Nope', 'adapter_key' => 'made_up'])->assertUnprocessable()->assertJsonValidationErrors('adapter_key');
        $this->postJson('/admin/api/suppliers', ['name' => 'Evil', 'adapter_key' => 'reloadly_giftcards', 'base_url_override' => 'https://169.254.169.254/latest'])
            ->assertUnprocessable()->assertJsonValidationErrors('base_url_override');
        $this->postJson('/admin/api/suppliers', ['name' => 'Ok', 'adapter_key' => 'reloadly_giftcards', 'base_url_override' => 'https://giftcards-sandbox.reloadly.com'])->assertCreated();
    });

    it('switches the active mode only to a mode with saved credentials, and audits it', function (): void {
        $supplier = Supplier::factory()->create(['adapter_key' => 'reloadly_giftcards', 'mode' => 'sandbox']);
        actingAsStaffWith(['suppliers.manage', 'suppliers.credentials', 'suppliers.view']);

        $this->patchJson("/admin/api/suppliers/{$supplier->id}", ['mode' => 'production'])
            ->assertUnprocessable()->assertJsonValidationErrors('mode');
        expect($supplier->fresh()->mode->value)->toBe('sandbox');

        $this->postJson("/admin/api/suppliers/{$supplier->id}/credentials", ['mode' => 'production', 'credentials' => ['client_id' => 'live-id', 'client_secret' => 'live-secret']])->assertOk();
        $this->patchJson("/admin/api/suppliers/{$supplier->id}", ['mode' => 'production'])->assertOk()->assertJsonPath('data.mode', 'production');

        expect($supplier->fresh()->mode->value)->toBe('production')
            ->and(DB::table('audit_logs')->where('action', 'suppliers.supplier.updated')->latest('id')->value('after'))->toContain('production');
    });

    it('stores credentials encrypted and never returns them', function (): void {
        $supplier = Supplier::factory()->create(['adapter_key' => 'reloadly_giftcards']);
        actingAsStaffWith(['suppliers.credentials', 'suppliers.view']);

        $response = $this->postJson("/admin/api/suppliers/{$supplier->id}/credentials", ['mode' => 'production', 'credentials' => ['client_id' => 'live-id', 'client_secret' => 'live-secret-xyz']])
            ->assertOk()->assertJsonPath('data.credentials_configured', ['sandbox' => false, 'production' => true]);
        $show = $this->getJson("/admin/api/suppliers/{$supplier->id}")->assertOk();

        expect($response->getContent().$show->getContent())->not->toContain('live-secret-xyz')->not->toContain('live-id')
            ->and(DB::table('supplier_credentials')->value('credentials'))->not->toContain('live-secret-xyz')
            ->and(SupplierCredential::query()->sole()->credentials['client_secret'])->toBe('live-secret-xyz')
            ->and(DB::table('audit_logs')->where('action', 'suppliers.credentials_set')->value('after'))->not->toContain('live-secret-xyz');
    });

    it('shows the capability matrix, the balance and failed request logs', function (): void {
        $supplier = reloadlySupplier();
        fakeReloadly(overrides: [RELOADLY_API.'/accounts/balance' => Http::response([], 500)]);
        expect(fn () => app(SupplierAdapterRegistry::class)->for($supplier)->getBalance())->toThrow(SupplierRequestFailed::class);
        actingAsStaffWith(['suppliers.view', 'suppliers.logs']);

        $this->getJson("/admin/api/suppliers/{$supplier->id}/capabilities")->assertOk()
            ->assertJsonPath('data.capabilities.cancel_order', 'none')
            ->assertJsonPath('data.capabilities.lookup_by_reference', 'full')
            ->assertJsonPath('data.capabilities.webhooks', 'unverified');
        $this->getJson("/admin/api/suppliers/{$supplier->id}/balance")->assertOk()->assertJsonPath('data.balance.amount', '1000.000000');
        $this->getJson("/admin/api/suppliers/{$supplier->id}/logs?filter[succeeded]=0")->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.operation', 'balance');
    });

    it('tests the connection and queues syncs', function (): void {
        $supplier = reloadlySupplier();
        fakeReloadly(overrides: [RELOADLY_API.'/products*' => Http::response(catalogBody())]);
        actingAsStaffWith(['suppliers.manage', 'suppliers.sync']);

        $this->postJson("/admin/api/suppliers/{$supplier->id}/test-connection")->assertOk()->assertJsonPath('data.reachable', true);
        $this->postJson("/admin/api/suppliers/{$supplier->id}/sync-catalog")->assertStatus(202);

        expect(SupplierProduct::query()->where('supplier_id', $supplier->id)->count())->toBe(1);
    });

    // The Reloadly sandbox balance endpoint can hang without answering (seen 2026-11-12), so Test
    // connection reads one product page instead and never depends on /accounts/balance.
    it('tests the connection with one product page, even while the balance endpoint fails', function (): void {
        $supplier = reloadlySupplier();
        fakeReloadly(overrides: [
            RELOADLY_API.'/accounts/balance' => fn () => throw new ConnectionException('timed out'),
            RELOADLY_API.'/products*' => Http::response(catalogBody()),
        ]);
        actingAsStaffWith(['suppliers.manage']);

        $this->postJson("/admin/api/suppliers/{$supplier->id}/test-connection")->assertOk()->assertJsonPath('data.reachable', true);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/products') && str_contains($request->url(), 'size=1'));
        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), '/accounts/balance'));
    });

    it('manages mappings with one primary per variant and a matching supplier product', function (): void {
        $variant = sellableVariant('9');
        $supplier = reloadlySupplier();
        $product = SupplierProduct::factory()->create(['supplier_id' => $supplier->id]);
        $foreign = SupplierProduct::factory()->create();
        actingAsStaffWith(['mappings.manage']);
        $body = ['product_variant_id' => $variant->id, 'supplier_id' => $supplier->id, 'supplier_product_id' => $product->id, 'role' => 'primary', 'supplier_unit_price' => '10', 'supplier_unit_currency' => 'USD'];

        $this->postJson('/admin/api/supplier-mappings', [...$body, 'supplier_product_id' => $foreign->id])->assertUnprocessable()->assertJsonValidationErrors('supplier_product_id');
        $this->postJson('/admin/api/supplier-mappings', $body)->assertCreated();

        $stock = stockSupplier();
        $this->postJson('/admin/api/supplier-mappings', [...$body, 'supplier_id' => $stock->id, 'supplier_product_id' => null])->assertUnprocessable();
        $this->postJson('/admin/api/supplier-mappings', ['product_variant_id' => $variant->id, 'supplier_id' => $stock->id, 'role' => 'primary', 'supplier_unit_price' => '10', 'supplier_unit_currency' => 'USD'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->postJson('/admin/api/supplier-mappings', ['product_variant_id' => $variant->id, 'supplier_id' => $stock->id, 'role' => 'secondary', 'supplier_unit_price' => '10', 'supplier_unit_currency' => 'USD', 'override_cost' => '8', 'override_cost_currency' => 'USD'])
            ->assertCreated()->assertJsonPath('data.supplier_product.external_id', 'variant-'.$variant->id);
    });
});

describe('catalog and price sync', function (): void {
    it('writes effective cost to supplier_product_prices, which the pricing engine then uses', function (): void {
        $supplier = reloadlySupplier();
        $variant = sellableVariant('99', ['manual_cost_base' => null]);
        fakeReloadly(overrides: [RELOADLY_API.'/products*' => Http::response(catalogBody())]);

        $run = app(SupplierCatalogSync::class)->run($supplier, 'test');
        $product = SupplierProduct::query()->sole();
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'supplier_id' => $supplier->id, 'supplier_product_id' => $product->id, 'role' => SupplierProductMappingRole::Primary, 'supplier_unit_price' => '25', 'supplier_unit_currency' => 'USD']);

        $quote = app(PricingEngine::class)->quote(ProductVariant::query()->find($variant->id), new PricingContext);

        expect($run->items_created)->toBe(1)
            ->and(SupplierProductPrice::query()->where('denomination', '25')->sole()->cost)->toBe('25.000000')
            ->and($quote->cost->amount)->toBe('25.000000');
    });

    it('marks products missing from a complete run as removed and refuses a duplicate window', function (): void {
        $supplier = reloadlySupplier();
        $stale = SupplierProduct::factory()->create(['supplier_id' => $supplier->id, 'external_id' => '999']);
        fakeReloadly(overrides: [RELOADLY_API.'/products*' => Http::response(catalogBody())]);

        app(SupplierCatalogSync::class)->run($supplier, 'scheduler', '2026-11-10');

        expect($stale->fresh()->removed_at)->not->toBeNull()
            ->and(app(SupplierCatalogSync::class)->run($supplier, 'scheduler', '2026-11-10'))->toBeNull();
    });

    it('writes a new price row only on change or when the last one is ageing', function (): void {
        $supplier = reloadlySupplier();
        $variant = sellableVariant('9');
        $detail = static fn (string $map): string => json_encode(json_decode(catalogBody($map), true)['content'][0]);
        fakeReloadly(overrides: [
            RELOADLY_API.'/products/1234' => Http::sequence()->push($detail('{"25":24.5,"50":49}'))->push($detail('{"25":26,"50":49}')),
            RELOADLY_API.'/products*' => Http::response(catalogBody()),
        ]);
        app(SupplierCatalogSync::class)->run($supplier, 'test');
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'supplier_id' => $supplier->id, 'supplier_product_id' => SupplierProduct::query()->value('id'), 'supplier_unit_price' => '25', 'supplier_unit_currency' => 'USD']);

        app(SupplierPriceSync::class)->run($supplier, 'test');
        expect(SupplierProductPrice::query()->count())->toBe(2);

        app(SupplierPriceSync::class)->run($supplier, 'test');
        expect(SupplierProductPrice::query()->where('denomination', '25')->latest('id')->value('cost'))->toBe('26.500000')
            ->and(ProductVariant::query()->find($variant->id)->pricing_dirty_at)->not->toBeNull();
    });

    it('prices range denominations from the synced exchange rate', function (): void {
        $supplier = reloadlySupplier();
        $product = SupplierProduct::factory()->create(['supplier_id' => $supplier->id, 'denomination_type' => 'range', 'sender_currency' => 'USD', 'min_denomination' => '5', 'max_denomination' => '500', 'cost_map' => ['rate' => '0.02'], 'fee_fixed' => '0.1']);
        SupplierProductMapping::factory()->create(['supplier_id' => $supplier->id, 'supplier_product_id' => $product->id, 'supplier_unit_price' => '250']);

        app(SupplierPriceWriter::class)->writeFor($product);

        expect(SupplierProductPrice::query()->sole()->cost)->toBe('5.100000');
    });
});

describe('balances, health and reconciliation', function (): void {
    it('caches the balance with a snapshot and opens the circuit after repeated failures', function (): void {
        $supplier = reloadlySupplier();
        fakeReloadly();

        app(SupplierMonitor::class)->refreshBalance($supplier);
        expect($supplier->fresh()->last_balance)->toBe('500.123450');

        Http::fake(['*' => Http::failedConnection('cURL error 7: Failed to connect')]);
        foreach (range(1, 3) as $check) {
            app(SupplierMonitor::class)->checkHealth($supplier->fresh());
        }

        expect($supplier->fresh()->health)->toBe(SupplierHealth::Down)
            ->and($supplier->fresh()->circuit_open_until->isFuture())->toBeTrue();
    });

    it('reports orphans, cost differences and missing transactions without changing anything', function (): void {
        $supplier = reloadlySupplier();
        $ours = SupplierTransaction::factory()->create(['supplier_id' => $supplier->id, 'external_transaction_id' => '100', 'status' => SupplierTransactionStatus::Succeeded, 'actual_cost' => '9.00', 'actual_cost_currency' => 'USD', 'submitted_at' => now()->subHour()]);
        $missing = SupplierTransaction::factory()->create(['supplier_id' => $supplier->id, 'external_transaction_id' => '200', 'status' => SupplierTransactionStatus::Succeeded, 'submitted_at' => now()->subHour()]);
        fakeReloadly(overrides: [RELOADLY_API.'/reports/transactions*' => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '100'), reloadlyTransaction('SUCCESSFUL', '300', 'uUNKNOWNs1')], 'last' => true])]);

        $run = app(SupplierReconciler::class)->run($supplier, CarbonImmutable::now());

        expect(ReconciliationItem::query()->pluck('type')->map->value->sort()->values()->all())->toBe(['amount_mismatch', 'missing_provider', 'orphan_provider'])
            ->and($run->mismatches)->toBe(3)
            ->and($ours->fresh()->status)->toBe(SupplierTransactionStatus::Succeeded)
            ->and($missing->fresh()->status)->toBe(SupplierTransactionStatus::Succeeded)
            ->and(ReconciliationItem::query()->where('type', ReconciliationItemType::OrphanProvider)->value('provider_reference'))->toBe('300');
    });
});

describe('margin floor in the payment currency (inherited from M7)', function (): void {
    it('adds the conversion cost when the payment currency is not the base currency', function (): void {
        app(SettingsService::class)->set('pricing', 'conversion_cost_pct', '10', SettingType::Decimal);
        app(SettingsService::class)->set('pricing', 'default_below_margin_policy', 'floor', SettingType::String);
        givenRate('EGP', '50');
        $variant = sellableVariant('10');

        $usd = app(PricingEngine::class)->quote($variant, new PricingContext(currency: 'USD'));
        $egp = app(PricingEngine::class)->quote($variant, new PricingContext(currency: 'USD', paymentCurrency: 'EGP'));

        expect($usd->floor->amount)->toBe('10.000000')
            ->and($egp->floor->amount)->toBe('11.111112')
            ->and($egp->baseAmount->amount)->toBe('11.111112');
    });

    it('refuses placement when charging another currency would break the margin', function (): void {
        app(SettingsService::class)->set('pricing', 'conversion_cost_pct', '10', SettingType::Decimal);
        givenRate('EGP', '50');
        CountryFactory::ensure('US');
        $variant = sellableVariant('10');
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated();

        fakePayments('EGP');
        $this->actingAs($user)->postJson('/api/v1/checkout/place-order', placeOrderBody('10.00'), ['Idempotency-Key' => 'pay-ccy-0001'])
            ->assertUnprocessable()->assertJsonPath('code', 'payment_currency_below_margin');

        fakePayments('USD');
        $this->actingAs($user)->postJson('/api/v1/checkout/place-order', placeOrderBody('10.00'), ['Idempotency-Key' => 'pay-ccy-0002'])->assertCreated();
    });
});

it('activates a supplier through the guarded status field only', function (): void {
    $supplier = Supplier::factory()->create(['adapter_key' => 'internal_stock']);
    actingAsStaffWith(['suppliers.manage']);

    $this->patchJson("/admin/api/suppliers/{$supplier->id}", ['status' => 'active'])->assertOk()->assertJsonPath('data.status', 'active');

    expect($supplier->fresh()->status)->toBe(SupplierStatus::Active);
});
