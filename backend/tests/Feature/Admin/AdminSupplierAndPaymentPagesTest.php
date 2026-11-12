<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierCredential;
use Database\Factories\Currency\CurrencyFactory;

/*
| M14: supplier pages (capability matrix, health, circuit, balance, write-only credentials) and
| payment pages (gateways with charge currency, write-only credentials, eligibility rules,
| reconciliation), with validation errors in the envelope.
*/

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
});

describe('suppliers', function (): void {
    it('renders the capability matrix across adapters with the current supplier, and the health block', function (): void {
        adminWith(['suppliers.view']);
        $reloadly = reloadlySupplier(['name' => 'Reloadly Gift Cards', 'circuit_open_until' => now()->addMinutes(10), 'last_balance' => '12840.2', 'balance_currency' => 'USD']);
        stockSupplier(['name' => 'Internal Stock']);

        $page = $this->get("/admin/suppliers/{$reloadly->id}");

        $page->assertOk()
            ->assertSee('Capability matrix')
            ->assertSee('lookup_by_reference')->assertSee('refund_order')->assertSee('per_request_amount_cap')
            ->assertSee('Internal Stock')
            ->assertSee('aria-current="true"', false)
            ->assertSee('Circuit open')
            ->assertSee('12840.20')
            ->assertSee('Awaiting sandbox verification');
        expect(substr_count($page->getContent(), 'text-status-success inline-flex') + substr_count($page->getContent(), 'inline-flex items-center gap-1.5 text-xs font-medium text-status-success'))->toBeGreaterThan(5);
        assertCspSafeHtml($page);
    });

    it('shows credentials only as configured, with the replace form only for suppliers.credentials', function (): void {
        $supplier = reloadlySupplier();
        SupplierCredential::query()->where('supplier_id', $supplier->id)->delete();
        SupplierCredential::factory()->create(['supplier_id' => $supplier->id, 'mode' => 'sandbox', 'credentials' => ['client_id' => ADMIN_CREDENTIAL_CANARY, 'client_secret' => ADMIN_CREDENTIAL_CANARY]]);

        adminWith(['suppliers.view']);
        $page = $this->get("/admin/suppliers/{$supplier->id}")->assertOk()->assertSee('Configured')
            ->assertDontSee('data-modal-open="credentials-sandbox"', false)->assertDontSee('name="credentials[client_secret]"', false);
        expect($page->getContent())->not->toContain(ADMIN_CREDENTIAL_CANARY);

        adminWith(['suppliers.view', 'suppliers.manage', 'suppliers.credentials']);
        $page = $this->get("/admin/suppliers/{$supplier->id}")
            ->assertSee('data-modal-open="credentials-sandbox"', false)->assertSee('data-modal-open="credentials-production"', false)
            ->assertSee('Replace Sandbox credentials')->assertSee('Replace Live credentials')
            ->assertSee('name="credentials[client_secret]"', false)->assertSee('Saved ')->assertSee('Never saved');
        $html = $page->getContent();
        expect($html)->not->toContain(ADMIN_CREDENTIAL_CANARY);
        // The write-only fields are never prefilled.
        preg_match_all('/<input[^>]*name="(credentials\[[a-z_]+\]|webhook_secret)"[^>]*>/', $html, $fields);
        expect($fields[0])->not->toBeEmpty();
        foreach ($fields[0] as $field) {
            expect($field)->toContain('type="password"')->toContain('value=""');
        }
        assertCspSafeHtml($page);
    });

    it('shows the Edit button and the edit dialog only with suppliers.manage', function (): void {
        $supplier = reloadlySupplier();

        adminWith(['suppliers.view']);
        $this->get("/admin/suppliers/{$supplier->id}")->assertOk()
            ->assertDontSee('data-supplier-edit', false)->assertDontSee('id="supplier-edit"', false)
            ->assertDontSee('data-mode-option', false);

        adminWith(['suppliers.view', 'suppliers.manage']);
        $this->get("/admin/suppliers/{$supplier->id}")->assertOk()
            ->assertSee('data-supplier-edit', false)->assertSee('id="supplier-edit"', false)
            ->assertSee('name="fallback_supplier_id"', false)->assertSee('name="max_fallback_attempts"', false)
            ->assertSee('name="retry_policy[max_attempts]"', false)->assertSee('data-error-for="name"', false)
            ->assertSee('Test connection')->assertDontSee('Sync catalog');
    });

    it('shows the Sandbox / Live control with the current mode and a real-money warning for Live', function (): void {
        $supplier = reloadlySupplier(['mode' => 'sandbox']);
        adminWith(['suppliers.view', 'suppliers.manage']);

        $this->get("/admin/suppliers/{$supplier->id}")->assertOk()
            ->assertSee('data-module="mode-switch"', false)
            ->assertSee('data-mode="sandbox"', false)->assertSee('data-mode="production"', false)
            ->assertSee('aria-pressed="true" data-mode-option data-mode="sandbox"', false)
            ->assertSee('data-mode-badge', false)
            ->assertSee('real money will be spent')
            ->assertSee('data-error-for="mode"', false);

        SupplierCredential::query()->where('supplier_id', $supplier->id)->where('mode', 'production')->delete();
        $this->patchJson("/admin/api/suppliers/{$supplier->id}", ['mode' => 'production'])->assertUnprocessable()->assertJsonValidationErrors('mode');
    });

    it('renders the supplier detail and list in Arabic, right to left', function (): void {
        $supplier = reloadlySupplier();
        adminWith(['suppliers.view', 'suppliers.manage', 'suppliers.credentials']);

        $this->withSession(['admin.locale' => 'ar'])->get("/admin/suppliers/{$supplier->id}")->assertOk()
            ->assertSee('dir="rtl"', false)->assertSee('مباشر')->assertSee('تعديل المورد');
        $this->withSession(['admin.locale' => 'ar'])->get('/admin/suppliers')->assertOk()->assertSee('dir="rtl"', false)->assertSee('إضافة مورد');
    });

    it('replaces supplier credentials through the write-only endpoint, audited without values', function (): void {
        $supplier = reloadlySupplier();
        $staff = adminWith(['suppliers.view', 'suppliers.credentials']);

        $this->postJson("/admin/api/suppliers/{$supplier->id}/credentials", ['mode' => 'sandbox', 'credentials' => ['client_id' => 'new-id', 'client_secret' => ADMIN_CREDENTIAL_CANARY]])
            ->assertOk()->assertJsonPath('data.credentials_configured.sandbox', true)
            ->assertDontSee(ADMIN_CREDENTIAL_CANARY);

        $audit = AuditLog::query()->where('action', 'suppliers.credentials_set')->where('actor_id', $staff->id)->sole();
        expect(json_encode($audit->after))->not->toContain(ADMIN_CREDENTIAL_CANARY);
    });

    it('shows supplier settings errors beside the fields from the envelope', function (): void {
        $supplier = reloadlySupplier();
        adminWith(['suppliers.view', 'suppliers.manage']);

        $this->get("/admin/suppliers/{$supplier->id}")->assertSee('data-error-for="low_balance_threshold"', false)->assertSee('data-error-for="priority"', false);
        $this->patchJson("/admin/api/suppliers/{$supplier->id}", ['priority' => -1, 'low_balance_threshold' => 'abc', 'status' => 'gone'])
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed')->assertJsonValidationErrors(['priority', 'low_balance_threshold', 'status']);
        $this->patchJson("/admin/api/suppliers/{$supplier->id}", ['fallback_enabled' => false, 'priority' => 3])->assertOk();

        expect($supplier->fresh()->priority)->toBe(3)
            ->and(AuditLog::query()->where('action', 'like', 'suppliers.supplier%')->count())->toBeGreaterThan(0);
    });

    it('shows the supplier list with summary cards', function (): void {
        adminWith(['suppliers.view']);
        reloadlySupplier(['last_balance' => '50', 'balance_currency' => 'USD', 'low_balance_threshold' => '100']);

        $this->get('/admin/suppliers')->assertOk()->assertSee('Below alert level')->assertSee('data-module="table"', false)->assertSee('1 active supplier')
            ->assertDontSee('id="supplier-create"', false);
    });

    it('offers Add supplier with suppliers.manage and creates one through the JSON route', function (): void {
        adminWith(['suppliers.view', 'suppliers.manage']);

        $this->get('/admin/suppliers')->assertOk()->assertSee('id="supplier-create"', false)->assertSee('name="adapter_key"', false)->assertSee('Add supplier');
        $this->postJson('/admin/api/suppliers', ['name' => 'New Supplier', 'adapter_key' => 'internal_stock', 'mode' => 'sandbox', 'priority' => 50, 'status' => 'inactive'])->assertCreated();

        expect(Supplier::query()->where('name', 'New Supplier')->exists())->toBeTrue();
    });
});

describe('payments', function (): void {
    it('lists gateways with mode, charge currency and credentials state, never values', function (): void {
        adminWith(['payments.view']);
        $paymob = gatewayFor('paymob', ['EGP'], defaultCurrency: 'EGP');
        $paymob->paymentGatewayCredentials()->update(['credentials' => ['api_key' => ADMIN_CREDENTIAL_CANARY, 'secret_key' => ADMIN_CREDENTIAL_CANARY, 'public_key' => 'pk']]);

        $page = $this->get('/admin/payments');

        $page->assertOk()->assertSee($paymob->name)->assertSee('EGP')->assertSee('Configured');
        expect($page->getContent())->not->toContain(ADMIN_CREDENTIAL_CANARY);
        assertCspSafeHtml($page);
    });

    it('shows the gateway page with eligibility rules and the OD-1 charge currency', function (): void {
        adminWith(['payments.view', 'payments.manage', 'payments.credentials']);
        $stripe = gatewayFor('stripe');

        $this->get("/admin/payments/gateways/{$stripe->id}")->assertOk()
            ->assertSee('Charge currency')->assertSee('USD')
            ->assertSee('Eligibility rules')->assertSee('Disable')
            ->assertSee('name="credentials[secret_key]"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
    });

    it('replaces gateway credentials, checking the Stripe key against the mode, audited by field name only', function (): void {
        $staff = adminWith(['payments.view', 'payments.credentials']);
        $stripe = gatewayFor('stripe');

        $this->postJson("/admin/api/payment-gateways/{$stripe->id}/credentials", ['mode' => 'sandbox', 'credentials' => ['secret_key' => 'sk_live_wrongmode', 'publishable_key' => 'pk_live_x1']])
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed')
            ->assertJsonValidationErrors(['credentials.secret_key', 'credentials.publishable_key']);
        $this->postJson("/admin/api/payment-gateways/{$stripe->id}/credentials", ['mode' => 'sandbox', 'credentials' => []])
            ->assertUnprocessable()->assertJsonValidationErrors(['credentials', 'credentials.secret_key']);

        $this->postJson("/admin/api/payment-gateways/{$stripe->id}/credentials", ['mode' => 'production', 'credentials' => ['secret_key' => 'sk_live_'.ADMIN_CREDENTIAL_CANARY, 'publishable_key' => 'pk_live_abc12345', 'extra' => 'dropped'], 'webhook_secret' => 'whsec_x'])
            ->assertOk()->assertJsonPath('data.credentials_configured.production.configured', true)->assertDontSee(ADMIN_CREDENTIAL_CANARY);

        $row = PaymentGatewayCredential::query()->where('payment_gateway_id', $stripe->id)->where('mode', 'production')->sole();
        expect(array_keys($row->credentials))->toBe(['secret_key', 'publishable_key']);
        $audit = AuditLog::query()->where('action', 'payments.credentials_set')->where('actor_id', $staff->id)->sole();
        expect($audit->after['keys'])->toBe(['secret_key', 'publishable_key'])->and(json_encode($audit->after))->not->toContain(ADMIN_CREDENTIAL_CANARY);
    });

    it('refuses to activate a gateway without credentials for its mode, beside the field', function (): void {
        $staff = adminWith(['payments.view', 'payments.manage']);
        $gateway = PaymentGateway::factory()->create(['is_active' => false, 'mode' => 'production', 'adapter_key' => 'paypal']);

        $this->patchJson("/admin/api/payment-gateways/{$gateway->id}", ['is_active' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('is_active');
        $this->patchJson("/admin/api/payment-gateways/{$gateway->id}", ['mode' => 'live'])->assertUnprocessable()->assertJsonValidationErrors('mode');

        $configured = gatewayFor('paypal');
        $this->patchJson("/admin/api/payment-gateways/{$configured->id}", ['is_active' => false])->assertOk()->assertJsonPath('data.is_active', false);
        expect(AuditLog::query()->where('action', 'payments.gateway_updated')->where('actor_id', $staff->id)->count())->toBe(1);
    });

    it('switches an eligibility rule on and off, audited', function (): void {
        $staff = adminWith(['payments.view', 'payments.manage']);
        $rule = gatewayFor('stripe')->paymentEligibilityRules()->firstOrFail();

        $this->patchJson("/admin/api/payment-eligibility-rules/{$rule->id}", ['is_active' => false])->assertOk();
        $this->patchJson("/admin/api/payment-eligibility-rules/{$rule->id}", ['is_active' => 'maybe'])->assertUnprocessable()->assertJsonValidationErrors('is_active');

        expect($rule->fresh()->is_active)->toBeFalse()
            ->and(AuditLog::query()->where('action', 'payments.eligibility_rule_toggled')->where('actor_id', $staff->id)->count())->toBe(1);
    });

    it('runs the open-payment reconciliation on demand, audited', function (): void {
        $staff = adminWith(['payments.view', 'payments.manage']);

        $this->postJson('/admin/api/payments/reconcile')->assertOk()->assertJsonStructure(['data' => ['payments', 'expired', 'refunds', 'webhooks']]);
        expect(AuditLog::query()->where('action', 'payments.reconciled')->where('actor_id', $staff->id)->count())->toBe(1);
    });

    it('resolves reconciliation differences one by one and in bulk, with a note', function (): void {
        $staff = adminWith(['payments.view', 'payments.manage']);
        [$one, $two, $three] = ReconciliationItem::factory()->count(3)->create(['status' => 'open'])->all();

        $this->postJson("/admin/api/reconciliation-items/{$one->id}/resolve", ['status' => 'resolved', 'note' => ''])->assertUnprocessable()->assertJsonValidationErrors('note');
        $this->postJson("/admin/api/reconciliation-items/{$one->id}/resolve", ['status' => 'resolved', 'note' => 'Matched the settlement report'])->assertOk()->assertJsonPath('data.changed', 1);
        $this->postJson('/admin/api/tables/reconciliation/bulk/ignore', ['ids' => [(string) $two->id, (string) $three->id, (string) $one->id]])->assertUnprocessable()->assertJsonValidationErrors('note');
        $this->postJson('/admin/api/tables/reconciliation/bulk/ignore', ['ids' => [(string) $two->id, (string) $three->id, (string) $one->id], 'note' => 'Test transactions'])
            ->assertOk()->assertJsonPath('data.changed', 2);

        expect($one->fresh()->status->value)->toBe('resolved')
            ->and($two->fresh()->status->value)->toBe('ignored')
            ->and($two->fresh()->resolved_by)->toBe($staff->id)
            ->and(AuditLog::query()->where('action', 'like', 'payments.reconciliation_item_%')->count())->toBe(3);
    });

    it('renders each payments tab with its table', function (string $path, string $text): void {
        adminWith(['payments.view', 'payments.webhook_logs', 'refunds.view']);

        $page = $this->get($path);
        $page->assertOk()->assertSee($text)->assertSee('data-module="table"', false);
        assertCspSafeHtml($page);
    })->with([
        ['/admin/payments/transactions', 'Every payment attempt'],
        ['/admin/payments/webhooks', 'Payloads stay encrypted'],
        ['/admin/payments/refunds', 'Create refunds from the order page'],
        ['/admin/payments/disputes', 'Evidence is submitted'],
        ['/admin/payments/reconciliation', 'daily reconciliation'],
    ]);
});

describe('dashboard', function (): void {
    it('shows KPIs, charts and blocks the staff member may see', function (): void {
        adminWith(['dashboard.view', 'reports.profit', 'suppliers.view', 'payments.view', 'orders.view']);
        reloadlySupplier(['name' => 'Reloadly Gift Cards', 'last_balance' => '100', 'balance_currency' => 'USD']);

        $page = $this->get('/admin/dashboard?days=7');

        $page->assertOk()
            ->assertSee('Net revenue')->assertSee('Net profit')->assertSee('Failed payments')
            ->assertSee('Supplier balances')->assertSee('Reloadly Gift Cards')
            ->assertSee('Gateway performance')->assertSee('Recent orders')
            ->assertSee('Last 7 days');
        assertCspSafeHtml($page);
    });

    it('leaves out profit, balances, gateways and orders without their permissions', function (): void {
        adminWith(['dashboard.view']);

        $this->get('/admin/dashboard')->assertOk()
            ->assertSee('Net revenue')
            ->assertDontSee('Net profit')->assertDontSee('Supplier balances')->assertDontSee('Gateway performance')->assertDontSee('Recent orders');
        $this->get('/admin/dashboard?days=11')->assertRedirect();
    });

    it('renders the dashboard in Arabic', function (): void {
        adminWith(['dashboard.view']);

        $this->withSession(['admin.locale' => 'ar'])->get('/admin/dashboard')->assertOk()->assertSee('dir="rtl"', false)->assertSee('صافي الإيرادات');
    });
});

it('has the same admin translation keys in English and Arabic', function (): void {
    $flatten = function (array $values, string $prefix = '') use (&$flatten): array {
        $keys = [];

        foreach ($values as $key => $value) {
            $keys = [...$keys, ...(is_array($value) ? $flatten($value, $prefix.$key.'.') : [$prefix.$key])];
        }

        return $keys;
    };

    expect($flatten(require lang_path('ar/admin.php')))->toEqualCanonicalizing($flatten(require lang_path('en/admin.php')));
});
