<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\Models\Country;
use Database\Factories\Currency\CurrencyFactory;

/*
| M15: settings groups, write-only secrets (canary), mobile app config, payment eligibility rule
| editing, the public payment key and supplier creation.
*/

const SETTINGS_SECRET_CANARY = 'oxr_CANARY_m15_settings_never_rendered_91c2';

dataset('setting groups', ['general', 'orders', 'checkout', 'pricing', 'currency', 'security', 'suppliers', 'notifications', 'mail', 'analytics', 'mobile', 'automation']);

describe('settings pages', function (): void {
    it('renders every group read-only for settings.view, in English and Arabic', function (string $group): void {
        adminWith(['orders.view']);
        $this->get('/admin/settings/'.$group)->assertForbidden();

        adminWith(['settings.view']);
        $page = $this->get('/admin/settings/'.$group)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        assertCspSafeHtml($page);
        $this->withSession(['admin.locale' => 'ar'])->get('/admin/settings/'.$group)->assertOk()->assertSee('dir="rtl"', false);
    })->with('setting groups');

    it('404s an unknown group', function (): void {
        adminWith(['settings.view']);
        $this->get('/admin/settings/nope')->assertNotFound();
    });

    it('shows guest checkout read-only from configuration', function (): void {
        config(['storefront.guest_checkout' => false]);
        adminWith(['settings.view', 'settings.manage']);

        $this->get('/admin/settings/checkout')->assertOk()->assertSee(__('admin.settings.field.guest_checkout'))->assertSee('off')
            ->assertDontSee('name="guest_checkout"', false);
    });
});

describe('saving', function (): void {
    it('saves a group with its permission, typed and audited, with envelope errors', function (): void {
        adminWith(['settings.view']);
        $this->putJson('/admin/api/settings/orders', ['max_payment_attempts' => 3, 'quote_ttl_minutes' => 30])->assertForbidden();

        adminWith(['settings.manage']);
        $this->putJson('/admin/api/settings/orders', ['max_payment_attempts' => 50, 'quote_ttl_minutes' => 30])->assertUnprocessable()->assertJsonValidationErrors('max_payment_attempts');
        $this->putJson('/admin/api/settings/orders', ['max_payment_attempts' => '3', 'quote_ttl_minutes' => 30])->assertOk();

        expect(app(SettingsService::class)->get('orders', 'max_payment_attempts'))->toBe(3)
            ->and(AuditLog::query()->where('action', 'settings.updated')->count())->toBe(1);
    });

    it('needs settings.manage_critical for critical groups', function (): void {
        adminWith(['settings.manage']);
        $this->putJson('/admin/api/settings/security', ['risk_review_score' => 30, 'risk_block_score' => 70, 'risk_first_order_amount_base' => '50'])
            ->assertForbidden()->assertJsonPath('code', 'forbidden');

        adminWith(['settings.manage_critical']);
        $this->putJson('/admin/api/settings/security', ['risk_review_score' => 30, 'risk_block_score' => 20, 'risk_first_order_amount_base' => '50'])
            ->assertUnprocessable()->assertJsonValidationErrors('risk_block_score');
        $this->putJson('/admin/api/settings/security', ['risk_review_score' => 30, 'risk_block_score' => 70, 'risk_first_order_amount_base' => '50.5'])->assertOk();

        expect(app(SettingsService::class)->get('security', 'risk_block_score'))->toBe(70)
            ->and(app(SettingsService::class)->get('security', 'risk_first_order_amount_base'))->toBe('50.5');
    });

    it('edits the mobile app config with its own validation (force update, web checkout redirect)', function (): void {
        adminWith(['settings.manage']);

        $this->putJson('/admin/api/settings/mobile', ['android_min_version' => 'one'])->assertUnprocessable()->assertJsonValidationErrors('android_min_version');
        $this->putJson('/admin/api/settings/mobile', ['android_min_version' => '1.4.0', 'web_checkout_redirect' => true, 'support_url' => '', 'maintenance_message' => null])->assertOk();

        $this->getJson('/api/v1/app/config')->assertOk()->assertJsonPath('data.config.features.web_checkout_redirect', true);
        expect(app(SettingsService::class)->get('app', 'android_min_version'))->toBe('1.4.0');
    });
});

describe('write-only secrets', function (): void {
    it('stores a secret encrypted, shows only configured, and never renders or audits the value', function (): void {
        adminWith(['settings.view', 'settings.manage']);
        $this->putJson('/admin/api/settings/currency/secrets/oxr_app_id', ['value' => SETTINGS_SECRET_CANARY])->assertForbidden();

        adminWith(['settings.view', 'settings.manage_critical']);
        $this->get('/admin/settings/currency')->assertSee(__('admin.common.not_configured'));
        $response = $this->putJson('/admin/api/settings/currency/secrets/oxr_app_id', ['value' => SETTINGS_SECRET_CANARY])->assertOk();

        $page = $this->get('/admin/settings/currency')->assertOk()->assertSee(__('admin.common.configured'));

        expect($page->getContent())->not->toContain(SETTINGS_SECRET_CANARY)
            ->and($response->getContent())->not->toContain(SETTINGS_SECRET_CANARY)
            ->and(Setting::query()->where('key', 'oxr_app_id')->value('value'))->not->toContain(SETTINGS_SECRET_CANARY)
            ->and(AuditLog::query()->get()->toJson())->not->toContain(SETTINGS_SECRET_CANARY)
            ->and(app(SettingsService::class)->get('currency', 'oxr_app_id'))->toBe(SETTINGS_SECRET_CANARY);

        foreach (array_keys((array) config('admin_settings.groups')) as $group) {
            expect($this->get('/admin/settings/'.$group)->getContent())->not->toContain(SETTINGS_SECRET_CANARY);
        }

        $this->putJson('/admin/api/settings/currency/secrets/oxr_app_id', ['value' => null])->assertOk();
        expect(app(SettingsService::class)->get('currency', 'oxr_app_id'))->toBeNull();
    });

    it('refuses an unknown secret key and secret rules', function (): void {
        adminWith(['settings.manage_critical']);

        $this->putJson('/admin/api/settings/currency/secrets/nope', ['value' => 'x'])->assertNotFound();
        $this->putJson('/admin/api/settings/currency/secrets/oxr_app_id', ['value' => 'short'])->assertUnprocessable()->assertJsonValidationErrors('value');
    });
});

describe('payment eligibility rules and public key', function (): void {
    it('creates, edits and deletes rules with payments.manage, audited', function (): void {
        Country::factory()->create(['code' => 'EG']);
        CurrencyFactory::ensure('EGP');
        $gateway = PaymentGateway::factory()->create();
        $rule = [
            'effect' => 'allow', 'countries' => ['eg'], 'ip_countries' => [], 'require_ip_match' => true, 'currencies' => ['EGP'],
            'min_amount_base' => null, 'max_amount_base' => '500', 'allow_guest' => false, 'max_risk_level' => 'medium', 'priority' => 10, 'is_active' => true,
        ];

        adminWith(['payments.view']);
        $this->postJson("/admin/api/payment-gateways/{$gateway->id}/eligibility-rules", $rule)->assertForbidden();

        adminWith(['payments.view', 'payments.manage']);
        $this->postJson("/admin/api/payment-gateways/{$gateway->id}/eligibility-rules", [...$rule, 'countries' => ['ZZ']])->assertUnprocessable()->assertJsonValidationErrors('countries.0');
        $id = $this->postJson("/admin/api/payment-gateways/{$gateway->id}/eligibility-rules", $rule)->assertCreated()->json('data.id');

        $saved = PaymentEligibilityRule::query()->findOrFail($id);
        expect($saved->countries)->toBe(['EG'])->and($saved->ip_countries)->toBeNull()->and($saved->max_risk_level->value)->toBe('medium');

        $this->putJson("/admin/api/payment-eligibility-rules/{$id}", [...$rule, 'effect' => 'deny'])->assertOk();
        $this->get('/admin/payments/gateways/'.$gateway->id)->assertOk()->assertSee(__('admin.gateways.edit_rule'));
        $this->deleteJson("/admin/api/payment-eligibility-rules/{$id}")->assertOk();

        expect(PaymentEligibilityRule::query()->whereKey($id)->exists())->toBeFalse()
            ->and(AuditLog::query()->whereIn('action', ['payments.eligibility_rule_created', 'payments.eligibility_rule_updated', 'payments.eligibility_rule_deleted'])->count())->toBe(3);
    });

    it('sets the Stripe publishable key, refuses secret keys and never renders the secret key', function (): void {
        $gateway = PaymentGateway::factory()->create(['key' => 'stripe', 'adapter_key' => 'stripe', 'mode' => 'sandbox']);
        PaymentGatewayCredential::factory()->create(['payment_gateway_id' => $gateway->id, 'mode' => 'sandbox', 'credentials' => ['secret_key' => ADMIN_CREDENTIAL_CANARY]]);

        adminWith(['payments.view', 'payments.manage']);
        $this->putJson("/admin/api/payment-gateways/{$gateway->id}/public-key", ['mode' => 'sandbox', 'value' => 'pk_test_1234567890'])->assertForbidden();

        adminWith(['payments.view', 'payments.credentials']);
        $this->putJson("/admin/api/payment-gateways/{$gateway->id}/public-key", ['mode' => 'sandbox', 'value' => 'sk_test_1234567890'])->assertUnprocessable()->assertJsonValidationErrors('value');
        $this->putJson("/admin/api/payment-gateways/{$gateway->id}/public-key", ['mode' => 'sandbox', 'value' => 'pk_test_1234567890'])->assertOk();

        $page = $this->get('/admin/payments/gateways/'.$gateway->id)->assertOk()->assertSee('pk_test_1234567890');
        expect($page->getContent())->not->toContain(ADMIN_CREDENTIAL_CANARY);
    });
});

it('offers supplier creation on the suppliers settings to suppliers.manage only', function (): void {
    adminWith(['settings.view']);
    $this->get('/admin/settings/suppliers')->assertOk()->assertDontSee(__('admin.settings.create_supplier'));

    adminWith(['settings.view', 'suppliers.manage']);
    $this->get('/admin/settings/suppliers')->assertOk()->assertSee(__('admin.settings.create_supplier'))->assertSee('internal_stock');
    $this->postJson('/admin/api/suppliers', ['name' => 'Stock room', 'adapter_key' => 'internal_stock', 'status' => 'inactive', 'mode' => 'sandbox', 'priority' => 100])->assertCreated();
});
