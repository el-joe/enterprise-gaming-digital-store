<?php

/**
 * M-A4 (gap G9): GET /api/v1/app/config, public, short-cached and free of secrets.
 */

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\Enums\EnvironmentMode;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

const APP_CONFIG_STRIPE_SECRET = 'sk_te' . 'st_51SecretValueThatMustNeverLeak0001';
const APP_CONFIG_WEBHOOK_SECRET = 'whsec_SecretWebhookValueNeverPublished';
const APP_CONFIG_PAYPAL_SECRET = 'PayPalClientSecretValueNeverPublished';
/** Hard query budget for one request with a cold cache (API_DOCUMENTATION.md 1d). */
const APP_CONFIG_QUERY_BUDGET = 8;

const APP_CONFIG_OXR_SECRET = 'oxr-app-id-secret-value-never-published';

function appConfig(array $query = [], array $headers = []): TestResponse
{
    return statelessCall('GET', 'api/v1/app/config'.($query === [] ? '' : '?'.http_build_query($query)), headers: $headers);
}

function stripeGateway(array $credentials = ['secret_key' => APP_CONFIG_STRIPE_SECRET], EnvironmentMode $mode = EnvironmentMode::Sandbox, bool $active = true): PaymentGateway
{
    CurrencyFactory::ensure('USD');

    $gateway = PaymentGateway::factory()->create(['key' => 'stripe', 'name' => 'Stripe', 'adapter_key' => 'stripe', 'is_active' => $active, 'mode' => $mode, 'default_currency' => 'USD']);
    PaymentGatewayCredential::factory()->create(['payment_gateway_id' => $gateway->id, 'mode' => $mode, 'credentials' => $credentials, 'webhook_secret' => APP_CONFIG_WEBHOOK_SECRET]);

    return $gateway;
}

describe('content', function (): void {
    it('returns versions, flags, locales, currencies, media base and links on both paths', function (string $path): void {
        CurrencyFactory::ensure('USD');
        config(['mobile.app.terms_url' => '/pages/terms', 'mobile.app.support_url' => 'https://help.boxcards.shop']);

        $response = statelessCall('GET', $path)
            ->assertOk()
            ->assertJsonPath('data.config.versions.android', ['min_supported_version' => '1.0.0', 'latest_version' => '1.0.0'])
            ->assertJsonPath('data.config.versions.ios', ['min_supported_version' => '1.0.0', 'latest_version' => '1.0.0'])
            ->assertJsonPath('data.config.update', null)
            ->assertJsonPath('data.config.maintenance', ['enabled' => false, 'message' => null])
            // OD-1: `guest_checkout_enabled` now follows `storefront.guest_checkout` (default off,
            // owner decision 4) and `web_checkout_redirect` is new (owner decision 3, default off).
            ->assertJsonPath('data.config.features', ['checkout_enabled' => true, 'guest_checkout_enabled' => false, 'wallet_enabled' => true, 'reviews_enabled' => true, 'support_enabled' => true, 'web_checkout_redirect' => false])
            ->assertJsonPath('data.config.links.web_checkout', rtrim((string) config('app.url'), '/').'/checkout')
            ->assertJsonPath('data.config.links.forgot_password', rtrim((string) config('app.url'), '/').'/forgot-password')
            ->assertJsonPath('data.config.locales', ['supported' => ['en', 'ar'], 'default' => 'en'])
            ->assertJsonPath('data.config.currencies.base', 'USD')
            ->assertJsonPath('data.config.links.terms', rtrim((string) config('app.url'), '/').'/pages/terms')
            ->assertJsonPath('data.config.links.support', 'https://help.boxcards.shop')
            ->assertJsonPath('data.config.links.privacy', null)
            ->assertJsonPath('data.config.media.base_url', rtrim((string) config('app.url'), '/').'/storage');

        expect($response->json('data.config.currencies.supported.*.code'))->toContain('USD')
            ->and($response->headers->get('Cache-Control'))->toContain('public')->toContain('max-age=60')
            ->and($response->headers->get('ETag'))->not->toBeNull();
    })->with(['api/v1/app/config', 'api/v1/mobile/app/config']);

    it('reads saved settings over the config defaults, without a deploy', function (): void {
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'android_min_version', 'value' => '2.3.0'])->assertSuccessful();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'checkout_enabled', 'value' => 'false'])->assertSuccessful();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'android_min_version', 'value' => '2.3'])->assertFailed();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'terms_url', 'value' => 'javascript:alert(1)'])->assertFailed();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'unknown', 'value' => 'x'])->assertFailed();

        appConfig()->assertOk()
            ->assertJsonPath('data.config.versions.android.min_supported_version', '2.3.0')
            ->assertJsonPath('data.config.features.checkout_enabled', false);
    });

    it('publishes guest checkout only while the server allows it (owner decision 4)', function (bool $server, bool $app, bool $published): void {
        config(['storefront.guest_checkout' => $server, 'mobile.app.guest_checkout_enabled' => $app]);

        appConfig()->assertOk()->assertJsonPath('data.config.features.guest_checkout_enabled', $published);
    })->with([
        'both on' => [true, true, true],
        'server off' => [false, true, false],
        'app off' => [true, false, false],
    ]);

    it('switches the web checkout redirect on without a deploy and publishes its URL (owner decision 3)', function (): void {
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'web_checkout_redirect', 'value' => 'true'])->assertSuccessful();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'web_checkout_url', 'value' => 'https://boxcards.shop/cart'])->assertSuccessful();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'web_checkout_url', 'value' => 'javascript:alert(1)'])->assertFailed();

        appConfig()->assertOk()
            ->assertJsonPath('data.config.features.web_checkout_redirect', true)
            ->assertJsonPath('data.config.links.web_checkout', 'https://boxcards.shop/cart');
    });

    it('publishes the forgot-password page the app opens, settable without a deploy (M-A10)', function (): void {
        appConfig()->assertOk()
            ->assertJsonPath('data.config.links.forgot_password', rtrim((string) config('app.url'), '/').'/forgot-password');

        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'forgot_password_url', 'value' => 'https://boxcards.shop/ar/forgot-password'])->assertSuccessful();
        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'forgot_password_url', 'value' => 'javascript:alert(1)'])->assertFailed();

        appConfig()->assertOk()
            ->assertJsonPath('data.config.links.forgot_password', 'https://boxcards.shop/ar/forgot-password');
    });

    it('turns maintenance on and off with a localized message', function (): void {
        $settings = app(SettingsService::class);

        $settings->set('app', 'maintenance', true, SettingType::Bool);
        appConfig()->assertOk()->assertJsonPath('data.config.maintenance', ['enabled' => true, 'message' => 'We are making improvements. Please try again shortly.']);
        appConfig(headers: ['Accept-Language' => 'ar'])->assertOk()->assertJsonPath('data.config.maintenance.message', 'نجري بعض التحسينات. يرجى المحاولة مرة أخرى بعد قليل.');

        $this->artisan('mobile:app-config', ['action' => 'set', 'key' => 'maintenance_message', 'value' => '{"en":"Back at 10:00","ar":"نعود الساعة 10:00"}'])->assertSuccessful();
        appConfig(headers: ['Accept-Language' => 'ar'])->assertOk()->assertJsonPath('data.config.maintenance.message', 'نعود الساعة 10:00');

        $settings->set('app', 'maintenance', false, SettingType::Bool);
        appConfig()->assertOk()->assertJsonPath('data.config.maintenance', ['enabled' => false, 'message' => null]);
    });
});

describe('force update', function (): void {
    beforeEach(function (): void {
        config(['mobile.app.android_min_version' => '1.4.0', 'mobile.app.android_latest_version' => '1.10.0', 'mobile.app.ios_min_version' => '2.0.0', 'mobile.app.ios_latest_version' => '2.0.0']);
    });

    it('requires an update strictly below the minimum and offers one below the latest', function (string $platform, string $version, bool $required, bool $available): void {
        appConfig(['platform' => $platform, 'version' => $version])
            ->assertOk()
            ->assertJsonPath('data.config.update', ['platform' => $platform, 'version' => $version, 'required' => $required, 'available' => $available]);
    })->with([
        'just below the minimum' => ['android', '1.3.9', true, true],
        'prerelease of the minimum' => ['android', '1.4.0-rc.1', true, true],
        'exactly the minimum' => ['android', '1.4.0', false, true],
        'numeric, not string, order' => ['android', '1.9.0', false, true],
        'exactly the latest' => ['android', '1.10.0', false, false],
        'build metadata ignored' => ['android', '1.10.0+42', false, false],
        'newer than the latest' => ['android', '1.11.0', false, false],
        'other platform' => ['ios', '1.99.99', true, true],
    ]);

    it('refuses a platform or version it cannot compare', function (array $query, string $field): void {
        appConfig($query)->assertUnprocessable()->assertJsonValidationErrors([$field]);
    })->with([
        'not semver' => [['platform' => 'android', 'version' => '1.4'], 'version'],
        'unknown platform' => [['platform' => 'windows', 'version' => '1.4.0'], 'platform'],
        'version without platform' => [['version' => '1.4.0'], 'platform'],
        'platform without version' => [['platform' => 'ios'], 'version'],
    ]);
});

describe('payment gateways', function (): void {
    it('publishes null for Stripe until a publishable key is configured, then the key', function (): void {
        stripeGateway();

        appConfig()->assertOk()->assertJsonPath('data.config.payment_gateways', ['stripe' => ['publishable_key' => null]]);

        $this->artisan('payments:public-key', ['gateway' => 'stripe', 'value' => 'pk_test_51PublicKeyValue'])->assertSuccessful();

        appConfig()->assertOk()->assertJsonPath('data.config.payment_gateways.stripe.publishable_key', 'pk_test_51PublicKeyValue');

        expect(PaymentGatewayCredential::query()->sole()->credentials)->toBe(['secret_key' => APP_CONFIG_STRIPE_SECRET, 'publishable_key' => 'pk_test_51PublicKeyValue']);

        $this->artisan('payments:public-key', ['gateway' => 'stripe', '--clear' => true])->assertSuccessful();
        appConfig()->assertOk()->assertJsonPath('data.config.payment_gateways.stripe.publishable_key', null);
    });

    it('refuses a secret, a key of the other mode, or a mode without credentials', function (array $arguments): void {
        stripeGateway();

        $this->artisan('payments:public-key', ['gateway' => 'stripe', ...$arguments])->assertFailed();

        expect(PaymentGatewayCredential::query()->sole()->credentials)->toBe(['secret_key' => APP_CONFIG_STRIPE_SECRET]);
    })->with([
        'secret key' => [['value' => APP_CONFIG_STRIPE_SECRET]],
        'restricted key' => [['value' => 'rk_test_51Restricted']],
        'live key on sandbox' => [['value' => 'pk_live_51PublicKeyValue']],
        'no production credentials' => [['value' => 'pk_live_51PublicKeyValue', '--mode' => 'production']],
        'unknown gateway' => [['gateway' => 'nope', 'value' => 'pk_test_51PublicKeyValue']],
    ]);

    it('publishes null for a stored value that does not match the gateway mode', function (): void {
        stripeGateway(['secret_key' => APP_CONFIG_STRIPE_SECRET, 'publishable_key' => 'pk_live_51StoredForTheWrongMode']);

        appConfig()->assertOk()->assertJsonPath('data.config.payment_gateways.stripe.publishable_key', null);
    });

    it('lists active gateways only, with no public field for the wallet', function (): void {
        stripeGateway(active: false);
        PaymentGateway::factory()->create(['key' => 'wallet', 'adapter_key' => 'wallet', 'is_active' => true]);

        appConfig()->assertOk()->assertJsonPath('data.config.payment_gateways', ['wallet' => []]);
    });

    it('never contains a secret value or a secret field name', function (): void {
        stripeGateway(['secret_key' => APP_CONFIG_STRIPE_SECRET, 'publishable_key' => 'pk_test_51PublicKeyValue']);
        $paypal = PaymentGateway::factory()->create(['key' => 'paypal', 'adapter_key' => 'paypal', 'is_active' => true]);
        PaymentGatewayCredential::factory()->create(['payment_gateway_id' => $paypal->id, 'mode' => $paypal->mode, 'credentials' => ['client_id' => 'AYPublicPayPalClientId', 'client_secret' => APP_CONFIG_PAYPAL_SECRET], 'webhook_secret' => APP_CONFIG_WEBHOOK_SECRET]);
        app(SettingsService::class)->set('currency', 'oxr_app_id', APP_CONFIG_OXR_SECRET, SettingType::String, encrypted: true);
        app(SettingsService::class)->set('app', 'maintenance', true, SettingType::Bool);
        // Settings from the database: the n8n secrets and the FCM key are encrypted settings applied
        // to config (or read by FcmClient); none may reach the public app config.
        app(SettingsService::class)->set('automation', 'outbound_secret', APP_CONFIG_OXR_SECRET.'-n8n', SettingType::String, encrypted: true);
        ['json' => $serviceAccount] = fakeServiceAccountJson('boxcards-appconfig');
        app(SettingsService::class)->set('push', 'fcm_service_account', $serviceAccount, SettingType::String, encrypted: true);

        $json = appConfig()->assertOk()
            ->assertJsonPath('data.config.payment_gateways.stripe.publishable_key', 'pk_test_51PublicKeyValue')
            ->assertJsonPath('data.config.payment_gateways.paypal.client_id', 'AYPublicPayPalClientId')
            ->getContent();

        foreach ([APP_CONFIG_STRIPE_SECRET, APP_CONFIG_WEBHOOK_SECRET, APP_CONFIG_PAYPAL_SECRET, APP_CONFIG_OXR_SECRET, (string) config('app.key'), 'PRIVATE KEY', 'boxcards-appconfig'] as $secret) {
            expect($json)->not->toContain($secret);
        }

        // Field names are checked on the JSON keys (M-A10: `links.forgot_password` is the public URL of
        // the web page `/forgot-password`, a name and value that legitimately contain "password").
        $keys = [];
        $collect = static function (mixed $node) use (&$collect, &$keys): void {
            if (is_array($node)) {
                foreach ($node as $key => $child) {
                    if (is_string($key)) {
                        $keys[] = strtolower($key);
                    }
                    $collect($child);
                }
            }
        };
        $collect(json_decode($json, true));
        $keys = array_values(array_diff($keys, ['forgot_password']));

        foreach (['secret', 'password', 'api_key', 'webhook', 'credential', 'token'] as $fieldName) {
            expect(implode(' ', $keys))->not->toContain($fieldName);
        }

        $withoutRecoveryLink = str_replace([(string) json_encode(appConfig()->json('data.config.links.forgot_password')), '"forgot_password"'], '', $json);

        foreach (['secret', 'password', 'api_key', 'webhook', 'credential', 'token'] as $fieldName) {
            expect(strtolower($withoutRecoveryLink))->not->toContain($fieldName);
        }
    });
});

it('stays within its query budget with a cold cache', function (): void {
    stripeGateway(['secret_key' => APP_CONFIG_STRIPE_SECRET, 'publishable_key' => 'pk_test_51PublicKeyValue']);
    PaymentGateway::factory()->create(['key' => 'wallet', 'adapter_key' => 'wallet', 'is_active' => true]);

    Cache::flush();
    DB::flushQueryLog();
    DB::enableQueryLog();
    appConfig(['platform' => 'android', 'version' => '1.0.0'], ['Accept-Language' => 'ar'])->assertOk();

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(APP_CONFIG_QUERY_BUDGET);
});
