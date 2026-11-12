<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Notifications\Services\FcmClient;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Payments\Services\HeaderGeoIpResolver;
use App\Domains\Payments\Services\NullGeoIpResolver;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Services\SettingDefinitions;
use App\Domains\Settings\Services\SettingsOverlay;
use App\Domains\Settings\Services\SettingsService;
use App\Providers\SettingsServiceProvider;
use Database\Seeders\SettingsSeeder;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/*
| Settings from the database (owner request 2026-11-12): the `.env` keys added by this project are
| admin settings applied over their config keys by SettingsOverlay. Overlay (database value wins,
| version invalidation, fail soft), encryption at rest, write-only rendering, permissions,
| validation, audit and `php artisan settings:import-env`.
*/

const DB_SETTINGS_OUTBOUND_CANARY = 'n8n-outbound-CANARY-db-settings-5f1e2d3c4b5a';

const DB_SETTINGS_AUTH_TOKEN_CANARY = 'n8n-header-CANARY-db-settings-0a9b8c7d6e5f';

const DB_SETTINGS_OXR_CANARY = 'oxr-CANARY-db-settings-77aa66bb';

/**
 * A second "process": an overlay with its own config repository holding the built-in defaults.
 *
 * @return array{0: SettingsOverlay, 1: Repository}
 */
function otherProcessOverlay(): array
{
    $config = new Repository([
        'admin_settings' => config('admin_settings'),
        'payments' => ['geoip' => ['driver' => 'null', 'header' => 'CF-IPCountry']],
        'automation' => ['outbox' => ['enabled' => false, 'secret' => ''], 'token_ttl_days' => 365],
        'storefront' => ['guest_checkout' => false],
    ]);

    return [new SettingsOverlay(app(SettingDefinitions::class), $config), $config];
}

describe('overlay', function (): void {
    it('applies a stored value over the config default, and a cleared one restores the default', function (): void {
        expect(config('payments.geoip.driver'))->toBe('null')
            ->and(app(GeoIpResolver::class))->toBeInstanceOf(NullGeoIpResolver::class);

        app(SettingsService::class)->set('geoip', 'geoip_driver', 'header', SettingType::String);
        app(SettingsService::class)->set('geoip', 'geoip_country_header', 'X-Country', SettingType::String);

        expect(config('payments.geoip.driver'))->toBe('header')
            ->and(config('payments.geoip.header'))->toBe('X-Country')
            ->and(app(GeoIpResolver::class))->toBeInstanceOf(HeaderGeoIpResolver::class);

        app(SettingsService::class)->set('geoip', 'geoip_driver', null, SettingType::String);

        expect(config('payments.geoip.driver'))->toBe('null')
            ->and(config('payments.geoip.header'))->toBe('X-Country');
    });

    it('lets the database value win when another process boots', function (): void {
        app(SettingsService::class)->set('checkout', 'guest_checkout', true, SettingType::Bool);
        app(SettingsService::class)->set('automation', 'automation_token_ttl_days', 30, SettingType::Int);

        [$overlay, $config] = otherProcessOverlay();
        $overlay->boot();

        expect($config->get('storefront.guest_checkout'))->toBeTrue()
            ->and($config->get('automation.token_ttl_days'))->toBe(30)
            ->and($config->get('payments.geoip.driver'))->toBe('null')
            ->and($overlay->defaultFor('storefront.guest_checkout'))->toBeFalse();
    });

    it('re-reads only after a save moved the version, with one cache read otherwise', function (): void {
        [$overlay, $config] = otherProcessOverlay();
        $overlay->boot();
        $version = $overlay->appliedVersion();

        DB::enableQueryLog();
        $overlay->refresh();
        expect(DB::getQueryLog())->toBe([])->and($overlay->appliedVersion())->toBe($version);
        DB::disableQueryLog();

        app(SettingsService::class)->set('geoip', 'geoip_driver', 'header', SettingType::String);
        expect($config->get('payments.geoip.driver'))->toBe('null');

        $overlay->refresh();

        expect($config->get('payments.geoip.driver'))->toBe('header')
            ->and($overlay->appliedVersion())->not->toBe($version)
            ->and(Cache::get(SettingsOverlay::VERSION_KEY))->toBe($overlay->appliedVersion());
    });

    it('re-checks before each job in a queue worker', function (): void {
        $overlay = app(SettingsOverlay::class);
        $overlay->watch();

        // Another process saved: the row and a new version, without this process knowing.
        (new Setting)->forceFill(['group' => 'checkout', 'key' => 'guest_checkout', 'value' => '1', 'type' => SettingType::Bool, 'is_encrypted' => false])->save();
        Cache::forever(SettingsOverlay::VERSION_KEY, 'saved-elsewhere');
        expect(config('storefront.guest_checkout'))->toBeFalse();

        event(new JobProcessing('redis', Mockery::mock(Job::class)->shouldIgnoreMissing()));

        expect(config('storefront.guest_checkout'))->toBeTrue();
        $overlay->watch(false);
    });

    it('does not re-check per job outside a worker', function (): void {
        app(SettingsOverlay::class)->watch(false);
        (new Setting)->forceFill(['group' => 'checkout', 'key' => 'guest_checkout', 'value' => '1', 'type' => SettingType::Bool, 'is_encrypted' => false])->save();
        Cache::forever(SettingsOverlay::VERSION_KEY, 'saved-elsewhere');

        event(new JobProcessing('sync', Mockery::mock(Job::class)->shouldIgnoreMissing()));

        expect(config('storefront.guest_checkout'))->toBeFalse();
    });

    it('fails soft without a database or without the settings table', function (string $connection): void {
        config(['database.connections.broken' => [...config('database.connections.mysql'), ...($connection === 'database' ? ['database' => 'gaming_store_missing_db'] : ['prefix' => 'missing_'])]]);
        $default = DB::getDefaultConnection();
        Cache::flush();
        DB::setDefaultConnection('broken');

        try {
            [$overlay, $config] = otherProcessOverlay();
            $overlay->boot();
            $overlay->refresh(force: true);

            expect($config->get('storefront.guest_checkout'))->toBeFalse()
                ->and($config->get('payments.geoip.driver'))->toBe('null')
                ->and($overlay->appliedVersion())->toBeNull();
        } finally {
            DB::setDefaultConnection($default);
            DB::purge('broken');
        }
    })->with(['database', 'table']);

    it('never applies while configuration is being cached', function (string $command): void {
        $argv = $_SERVER['argv'];
        $_SERVER['argv'] = ['artisan', $command];
        $overlay = Mockery::mock(SettingsOverlay::class);
        $overlay->shouldNotReceive('boot');
        app()->instance(SettingsOverlay::class, $overlay);

        try {
            (new SettingsServiceProvider(app()))->boot();
        } finally {
            $_SERVER['argv'] = $argv;
        }
    })->with(['config:cache', 'optimize']);

    it('keeps no env() call for a moved key in the config files', function (): void {
        $moved = array_filter(array_map(static fn ($definition): ?string => $definition->envKey, app(SettingDefinitions::class)->all()));

        foreach (glob(config_path('*.php')) as $file) {
            foreach ($moved as $key) {
                expect(file_get_contents($file))->not->toContain("env('{$key}'");
            }
        }

        expect(count($moved))->toBeGreaterThanOrEqual(36);
    });
});

describe('secrets', function (): void {
    it('stores the n8n and OXR secrets encrypted, applies them to config and never renders, returns or audits them', function (): void {
        adminWith(['settings.view', 'settings.manage_critical']);

        foreach ([['automation', 'outbound_secret', DB_SETTINGS_OUTBOUND_CANARY], ['automation', 'webhook_auth_token', DB_SETTINGS_AUTH_TOKEN_CANARY], ['currency', 'oxr_app_id', DB_SETTINGS_OXR_CANARY]] as [$group, $key, $canary]) {
            $response = $this->putJson("/admin/api/settings/{$group}/secrets/{$key}", ['value' => $canary])->assertOk()->assertJsonPath('data.configured', true);
            expect($response->getContent())->not->toContain($canary);

            $stored = Setting::query()->where('group', $group)->where('key', $key)->firstOrFail();
            expect($stored->is_encrypted)->toBeTrue()
                ->and($stored->getRawOriginal('value'))->not->toContain($canary)
                ->and(Crypt::decryptString($stored->getRawOriginal('value')))->toBe($canary);
        }

        expect(config('automation.outbox.secret'))->toBe(DB_SETTINGS_OUTBOUND_CANARY)
            ->and(config('automation.outbox.auth_token'))->toBe(DB_SETTINGS_AUTH_TOKEN_CANARY)
            ->and(config('services.open_exchange_rates.app_id'))->toBe(DB_SETTINGS_OXR_CANARY);

        $audit = AuditLog::query()->where('action', 'settings.updated')->get();
        expect($audit)->toHaveCount(3)
            ->and($audit->toJson())->not->toContain(DB_SETTINGS_OUTBOUND_CANARY)->not->toContain(DB_SETTINGS_AUTH_TOKEN_CANARY)->not->toContain(DB_SETTINGS_OXR_CANARY)
            ->and($audit->pluck('after')->all())->each->toBe(['value' => 'changed']);

        $this->get('/admin/settings/automation')->assertOk()->assertSee(__('admin.common.configured'))
            ->assertDontSee(DB_SETTINGS_OUTBOUND_CANARY)->assertDontSee(DB_SETTINGS_AUTH_TOKEN_CANARY);

        $this->putJson('/admin/api/settings/automation/secrets/outbound_secret', ['value' => null])->assertOk()->assertJsonPath('data.configured', false);
        expect(config('automation.outbox.secret'))->toBe('')
            ->and(AuditLog::query()->latest('id')->firstOrFail()->after)->toBe(['value' => 'cleared']);
    });

    it('takes the FCM service-account key pasted or uploaded, validated, encrypted and read by FcmClient only', function (): void {
        ['json' => $json] = fakeServiceAccountJson('boxcards-prod', 'sender@boxcards-prod.iam.gserviceaccount.com');
        $privateKey = json_decode($json, true)['private_key'];
        adminWith(['settings.view', 'settings.manage', 'settings.manage_critical']);

        $this->get('/admin/settings/push')->assertOk()->assertSee('data-multipart', false)->assertSee(__('admin.common.not_configured'));

        $this->putJson('/admin/api/settings/push/secrets/fcm_service_account', ['value' => json_encode(['type' => 'service_account', 'project_id' => 'boxcards-prod', 'client_email' => 'a@b.test'])])
            ->assertUnprocessable()->assertJsonValidationErrors('value');
        $this->putJson('/admin/api/settings/push/secrets/fcm_service_account', ['value' => 'not json'])->assertUnprocessable()->assertJsonValidationErrors('value');

        $upload = UploadedFile::fake()->createWithContent('service-account.json', $json);
        $response = $this->put('/admin/api/settings/push/secrets/fcm_service_account', ['file' => $upload], ['Accept' => 'application/json'])->assertOk();
        expect($response->getContent())->not->toContain('PRIVATE KEY');

        $stored = Setting::query()->where('group', 'push')->where('key', 'fcm_service_account')->firstOrFail();
        expect($stored->is_encrypted)->toBeTrue()
            ->and($stored->getRawOriginal('value'))->not->toContain('PRIVATE KEY')
            ->and(app(FcmClient::class)->credentials())->toMatchArray(['client_email' => 'sender@boxcards-prod.iam.gserviceaccount.com', 'project_id' => 'boxcards-prod'])
            ->and(json_encode([config('push'), config('services')]))->not->toContain('PRIVATE KEY');

        $page = $this->get('/admin/settings/push')->assertOk()->assertSee(__('admin.common.configured'));
        expect($page->getContent())->not->toContain('PRIVATE KEY')->not->toContain(substr($privateKey, 40, 40))
            ->and(AuditLog::query()->get()->toJson())->not->toContain('PRIVATE KEY');

        $this->putJson('/admin/api/settings/push/secrets/fcm_service_account', ['value' => $json])->assertOk();
        $this->putJson('/admin/api/settings/push/secrets/fcm_service_account', ['value' => null])->assertOk();
        expect(app(SettingsService::class)->has('push', 'fcm_service_account'))->toBeFalse();
    });

    it('refuses a file upload for a secret that is not an upload', function (): void {
        adminWith(['settings.manage_critical']);
        $upload = UploadedFile::fake()->createWithContent('secret.json', '{"a":1}');

        $this->put('/admin/api/settings/automation/secrets/outbound_secret', ['file' => $upload], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('file');
    });
});

describe('permissions', function (): void {
    dataset('critical moved groups', [
        'checkout' => ['checkout', ['guest_checkout' => true]],
        'geoip' => ['geoip', ['geoip_driver' => 'header', 'geoip_country_header' => 'X-Country']],
        'app links' => ['app_links', ['android_packages' => 'shop.boxcards.app.staging', 'biometric_challenge_ttl_seconds' => 90]],
        'account links' => ['account_links', ['password_reset_url' => 'https://boxcards.shop/reset/{token}?email={email}']],
        'push' => ['push', ['push_enabled' => true, 'fcm_project_id' => 'boxcards-prod']],
        'admin idle' => ['security', ['admin_idle_minutes' => 20, 'risk_review_score' => 30, 'risk_block_score' => 70, 'risk_first_order_amount_base' => '50']],
        'automation' => ['automation', ['outbox_enabled' => true, 'webhook_url' => 'https://n8n.boxcards.shop/webhook/egs-events']],
        // Owner decision 11.
        'address and sessions' => ['session', ['app_url' => 'https://boxcards.shop', 'session_lifetime' => 60, 'session_secure_cookie' => true, 'session_same_site' => 'strict']],
        'mail' => ['mail', ['mailer' => 'smtp', 'smtp_host' => 'smtp.boxcards.shop', 'smtp_port' => 587, 'from_address' => 'orders@boxcards.shop']],
        'storage' => ['storage', ['sitemap_disk' => 'public', 'media_base_url' => 'https://cdn.boxcards.shop', 'aws_bucket' => 'boxcards-media']],
        'token lifetimes' => ['security', ['risk_review_score' => 30, 'risk_block_score' => 70, 'risk_first_order_amount_base' => '50', 'password_timeout' => 900, 'mobile_access_token_ttl_minutes' => 20, 'guest_order_token_ttl_hours' => 12, 'api_token_prefix' => 'bxc_']],
        'exchange rates api' => ['currency', ['max_age_minutes' => 720, 'max_change_pct' => '12', 'oxr_base_url' => 'https://oxr.example.com/api/', 'oxr_timeout' => 10]],
    ]);

    dataset('standard moved groups', [
        'payments' => ['payments', ['return_url' => '/checkout/orders/{order}/done', 'stripe_app_return_url' => 'boxcards://stripe-redirect']],
        'mcp' => ['mcp', ['pending_action_ttl_minutes' => 30, 'coupon_tier1_max_percent' => '15', 'coupon_tier1_max_fixed_base' => '5.50', 'mcp_token_ttl_days' => 30]],
        'seo' => ['seo', ['block_all_crawlers' => true]],
        'admin theme' => ['admin', ['admin_theme' => 'light']],
        // Owner decision 11.
        'store and languages' => ['site', ['app_name' => 'Boxcards', 'brand_name' => 'Boxcards Shop', 'locale' => 'ar', 'faker_locale' => 'ar_SA']],
        'affiliates' => ['affiliates', ['attribution_days' => 45]],
        'gateway timeouts' => ['payments', ['http_connect_timeout' => 3, 'http_timeout' => 30]],
    ]);

    it('needs settings.manage_critical for security-sensitive groups', function (string $group, array $values): void {
        adminWith(['settings.manage']);
        $this->putJson('/admin/api/settings/'.$group, $values)->assertForbidden()->assertJsonPath('code', 'forbidden');
        expect(Setting::query()->where('group', $group)->exists())->toBeFalse();

        adminWith(['settings.manage_critical']);
        $this->putJson('/admin/api/settings/'.$group, $values)->assertOk();
        expect(Setting::query()->where('group', $group)->count())->toBe(count($values));
    })->with('critical moved groups');

    it('needs settings.manage for the other moved groups', function (string $group, array $values): void {
        adminWith(['settings.view']);
        $this->putJson('/admin/api/settings/'.$group, $values)->assertForbidden();

        adminWith(['settings.manage']);
        $this->putJson('/admin/api/settings/'.$group, $values)->assertOk();
        expect(Setting::query()->where('group', $group)->count())->toBe(count($values));
    })->with('standard moved groups');

    it('applies the saved values to the config keys running code reads', function (): void {
        adminWith(['settings.view', 'settings.manage', 'settings.manage_critical']);

        $this->putJson('/admin/api/settings/mcp', ['pending_action_ttl_minutes' => 30, 'coupon_tier1_max_percent' => '15', 'mcp_token_ttl_days' => 30])->assertOk();
        $this->putJson('/admin/api/settings/app_links', ['android_sha256_fingerprints' => str_repeat('AB:', 31).'CD', 'ios_app_ids' => 'ABCDE12345.shop.boxcards.app'])->assertOk();
        $this->putJson('/admin/api/settings/seo', ['block_all_crawlers' => true])->assertOk();
        $this->putJson('/admin/api/settings/admin', ['admin_theme' => 'light'])->assertOk();

        expect(config('mcp.pending_action_ttl_minutes'))->toBe(30)
            ->and(config('mcp.coupon_tier1_max_percent'))->toBe('15')
            ->and(config('mcp.token_ttl_days'))->toBe(30)
            ->and(config('mobile.app_links.ios_app_ids'))->toBe('ABCDE12345.shop.boxcards.app')
            ->and(config('admin.theme'))->toBe('light');

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
        $this->get('/.well-known/apple-app-site-association')->assertOk()->assertJsonPath('applinks.details.0.appIDs', ['ABCDE12345.shop.boxcards.app']);
        $this->get('/admin/settings/admin')->assertOk()->assertSee('data-theme="admin-light"', false);
    });
});

describe('validation', function (): void {
    dataset('invalid values', [
        'return url script' => ['payments', 'return_url', 'javascript:alert(1)//{order}'],
        'return url without {order}' => ['payments', 'return_url', '/checkout/return'],
        'return url protocol-relative' => ['payments', 'return_url', '//evil.test/{order}'],
        'app return url on another host' => ['payments', 'app_return_url', 'https://evil.test/app/{order}'],
        'stripe return url data scheme' => ['payments', 'stripe_app_return_url', 'data://text/html'],
        'reset url http outside local' => ['account_links', 'password_reset_url', 'http://boxcards.shop/reset/{token}'],
        'reset url without {token}' => ['account_links', 'password_reset_url', 'https://boxcards.shop/reset'],
        'reset url with user info' => ['account_links', 'password_reset_url', 'https://user:pass@boxcards.shop/{token}'],
        'n8n webhook http outside local' => ['automation', 'webhook_url', 'http://n8n.example.com/webhook/egs-events'],
        'short fingerprint' => ['app_links', 'android_sha256_fingerprints', 'AB:CD:EF'],
        'fingerprint list with a bad item' => ['app_links', 'android_sha256_fingerprints', str_repeat('AB:', 31).'CD,ZZ'],
        'package id' => ['app_links', 'android_packages', 'shop.boxcards.app,1bad'],
        'apple app id without team' => ['app_links', 'ios_app_ids', 'shop.boxcards.app'],
        'biometric ttl too long' => ['app_links', 'biometric_challenge_ttl_seconds', 3600],
        'geoip driver' => ['geoip', 'geoip_driver', 'maxmind'],
        'geoip header' => ['geoip', 'geoip_country_header', 'CF IPCountry'],
        'admin theme' => ['admin', 'admin_theme', 'blue'],
        'admin idle too short' => ['security', 'admin_idle_minutes', 1],
        'mcp ttl zero' => ['mcp', 'pending_action_ttl_minutes', 0],
        'mcp coupon cap above 100' => ['mcp', 'coupon_tier1_max_percent', '150'],
        'mcp coupon fixed not a decimal' => ['mcp', 'coupon_tier1_max_fixed_base', '1e3'],
        'automation ttl' => ['automation', 'automation_token_ttl_days', 9999],
        'fcm project id' => ['push', 'fcm_project_id', 'Bad_Project'],
        // Owner decision 11.
        'store address http outside local' => ['session', 'app_url', 'http://boxcards.shop'],
        'store address not a url' => ['session', 'app_url', 'boxcards.shop'],
        'session lifetime zero' => ['session', 'session_lifetime', 0],
        'session path without slash' => ['session', 'session_path', 'account'],
        'session domain with a scheme' => ['session', 'session_domain', 'https://boxcards.shop'],
        'session same site' => ['session', 'session_same_site', 'sometimes'],
        'session cookie name' => ['session', 'session_cookie', 'bad cookie;'],
        'mailer not offered' => ['mail', 'mailer', 'array'],
        'smtp scheme' => ['mail', 'smtp_scheme', 'tls'],
        'smtp host with a scheme' => ['mail', 'smtp_host', 'https://smtp.boxcards.shop'],
        'smtp port' => ['mail', 'smtp_port', 70000],
        'from address' => ['mail', 'from_address', 'not-an-email'],
        'ehlo domain' => ['mail', 'ehlo_domain', 'bad host!'],
        's3 without its driver' => ['storage', 'filesystem_disk', 's3'],
        'unknown disk' => ['storage', 'sitemap_disk', 'ftp'],
        'media url http outside local' => ['storage', 'media_base_url', 'http://cdn.boxcards.shop'],
        'bucket name' => ['storage', 'aws_bucket', 'Bad_Bucket'],
        'region' => ['storage', 'aws_default_region', 'EU West'],
        'locale not supported' => ['site', 'locale', 'fr'],
        'faker locale' => ['site', 'faker_locale', 'english'],
        'empty store name' => ['site', 'app_name', ''],
        'token prefix' => ['security', 'api_token_prefix', 'Bad-Prefix'],
        'password timeout' => ['security', 'password_timeout', 10],
        'app access token too long' => ['security', 'mobile_access_token_ttl_minutes', 100000],
        'rates api http' => ['currency', 'oxr_base_url', 'http://openexchangerates.org/api/'],
        'gateway timeout' => ['payments', 'http_timeout', 0],
        'attribution days' => ['affiliates', 'attribution_days', 0],
    ]);

    it('refuses a bad value with an envelope error beside the field', function (string $group, string $key, mixed $value): void {
        adminWith(['settings.manage', 'settings.manage_critical']);

        $this->putJson('/admin/api/settings/'.$group, [$key => $value])->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')->assertJsonValidationErrors($key);
        expect(Setting::query()->where('group', $group)->where('key', $key)->exists())->toBeFalse();
    })->with('invalid values');

    it('accepts http URLs only while the app runs locally', function (): void {
        adminWith(['settings.manage_critical']);
        config(['app.env' => 'local']);

        $this->putJson('/admin/api/settings/automation', ['webhook_url' => 'http://n8n.boxcards.loc/webhook/egs-events'])->assertOk();
        expect(config('automation.outbox.webhook_url'))->toBe('http://n8n.boxcards.loc/webhook/egs-events');
    });

    it('accepts lower-case fingerprints and several packages', function (): void {
        adminWith(['settings.manage_critical']);

        $this->putJson('/admin/api/settings/app_links', [
            'android_packages' => 'shop.boxcards.app, shop.boxcards.app.staging',
            'android_sha256_fingerprints' => strtolower(str_repeat('AB:', 31).'CD'),
        ])->assertOk();

        $this->get('/.well-known/assetlinks.json')->assertOk()->assertJsonCount(2)->assertJsonPath('0.target.sha256_cert_fingerprints', [str_repeat('AB:', 31).'CD']);
    });
});

describe('audit', function (): void {
    it('records the old and new value of every changed non-secret setting', function (): void {
        adminWith(['settings.manage_critical']);

        $risk = ['risk_review_score' => 40, 'risk_block_score' => 80, 'risk_first_order_amount_base' => '100'];
        $this->putJson('/admin/api/settings/security', [...$risk, 'admin_idle_minutes' => 20])->assertOk();
        $this->putJson('/admin/api/settings/security', [...$risk, 'admin_idle_minutes' => 45])->assertOk();
        $this->putJson('/admin/api/settings/security', [...$risk, 'admin_idle_minutes' => 45])->assertOk();

        $entries = AuditLog::query()->where('action', 'settings.updated')->orderBy('id')->get();

        expect($entries)->toHaveCount(2)
            ->and($entries[0]->before)->toBeNull()->and($entries[0]->after)->toBe(['value' => 20])
            ->and($entries[1]->before)->toBe(['value' => '20'])->and($entries[1]->after)->toBe(['value' => 45])
            ->and(config('identity.admin_idle_minutes'))->toBe(45);
    });
});

describe('settings:import-env', function (): void {
    // Every moved key starts unset, whatever the machine's own environment holds, and is restored
    // to unset afterwards (the test process never keeps a value it set).
    beforeEach(function (): void {
        $this->movedKeys = array_values(array_filter(array_map(static fn ($definition): ?string => $definition->envKey, app(SettingDefinitions::class)->all())));
        withoutEnvironment($this->movedKeys);
    });
    afterEach(fn () => withoutEnvironment($this->movedKeys));

    it('copies the current values once, prints key names and results only, never a value', function (): void {
        withEnvironment([
            'GEOIP_DRIVER' => 'header',
            'GEOIP_COUNTRY_HEADER' => 'X-Unique-Country-Hdr',
            'N8N_OUTBOUND_SECRET' => DB_SETTINGS_OUTBOUND_CANARY,
            'STOREFRONT_GUEST_CHECKOUT' => 'true',
            'MCP_TOKEN_TTL_DAYS' => '47',
            'MOBILE_WEB_CHECKOUT_REDIRECT' => 'true',
        ]);

        $exit = Artisan::call('settings:import-env');
        $output = Artisan::output();

        expect($exit)->toBe(0)
            ->and($output)->not->toContain(DB_SETTINGS_OUTBOUND_CANARY)->not->toContain('X-Unique-Country-Hdr')->not->toContain('47')
            ->and($output)->toMatch('/GEOIP_DRIVER\s*\|\s*geoip\.geoip_driver\s*\|\s*imported/')
            ->and($output)->toMatch('/N8N_OUTBOUND_SECRET\s*\|\s*automation\.outbound_secret\s*\|\s*imported/')
            ->and($output)->toMatch('/N8N_WEBHOOK_AUTH_TOKEN\s*\|\s*automation\.webhook_auth_token\s*\|\s*empty/')
            ->and(app(SettingsService::class)->get('geoip', 'geoip_driver'))->toBe('header')
            ->and(app(SettingsService::class)->get('checkout', 'guest_checkout'))->toBeTrue()
            ->and(app(SettingsService::class)->get('mcp', 'mcp_token_ttl_days'))->toBe(47)
            ->and(app(SettingsService::class)->get('app', 'web_checkout_redirect'))->toBeTrue()
            ->and(app(SettingsService::class)->get('automation', 'outbound_secret'))->toBe(DB_SETTINGS_OUTBOUND_CANARY)
            ->and(Setting::query()->where('key', 'outbound_secret')->value('is_encrypted'))->toBeTrue()
            ->and(config('payments.geoip.driver'))->toBe('header')
            ->and(config('automation.outbox.secret'))->toBe(DB_SETTINGS_OUTBOUND_CANARY)
            ->and(AuditLog::query()->get()->toJson())->not->toContain(DB_SETTINGS_OUTBOUND_CANARY);
    });

    it('is idempotent: a key with a database value is skipped unless forced', function (): void {
        withEnvironment(['GEOIP_COUNTRY_HEADER' => 'X-Env-One', 'N8N_OUTBOUND_SECRET' => DB_SETTINGS_OUTBOUND_CANARY]);
        Artisan::call('settings:import-env');
        expect(app(SettingsService::class)->get('geoip', 'geoip_country_header'))->toBe('X-Env-One');

        Artisan::call('settings:import-env');
        expect(Artisan::output())->toMatch('/GEOIP_COUNTRY_HEADER\s*\|\s*geoip\.geoip_country_header\s*\|\s*skipped/');

        app(SettingsService::class)->set('geoip', 'geoip_country_header', 'X-Admin-Set', SettingType::String);
        withEnvironment(['GEOIP_COUNTRY_HEADER' => 'X-Env-Two', 'N8N_OUTBOUND_SECRET' => DB_SETTINGS_AUTH_TOKEN_CANARY]);

        Artisan::call('settings:import-env');
        expect(Artisan::output())->toMatch('/GEOIP_COUNTRY_HEADER\s*\|\s*geoip\.geoip_country_header\s*\|\s*skipped/')->toMatch('/N8N_OUTBOUND_SECRET\s*\|\s*automation\.outbound_secret\s*\|\s*skipped/')
            ->and(app(SettingsService::class)->get('geoip', 'geoip_country_header'))->toBe('X-Admin-Set')
            ->and(app(SettingsService::class)->get('automation', 'outbound_secret'))->toBe(DB_SETTINGS_OUTBOUND_CANARY);

        Artisan::call('settings:import-env', ['--force' => true]);
        expect(Artisan::output())->not->toContain(DB_SETTINGS_AUTH_TOKEN_CANARY)->not->toContain('X-Env-Two')
            ->and(app(SettingsService::class)->get('geoip', 'geoip_country_header'))->toBe('X-Env-Two')
            ->and(app(SettingsService::class)->get('automation', 'outbound_secret'))->toBe(DB_SETTINGS_AUTH_TOKEN_CANARY);
    });

    it('imports over a seeded built-in default that staff never changed', function (): void {
        $this->seed(SettingsSeeder::class);
        expect(Setting::query()->where('group', 'admin')->where('key', 'admin_theme')->value('value'))->toBe('dark');
        withEnvironment(['ADMIN_THEME' => 'light']);

        Artisan::call('settings:import-env');

        expect(Artisan::output())->toMatch('/ADMIN_THEME\s*\|\s*admin\.admin_theme\s*\|\s*imported/')
            ->and(app(SettingsService::class)->get('admin', 'admin_theme'))->toBe('light');
    });

    it('imports the FCM service-account JSON from the file at the old FCM_CREDENTIALS_PATH', function (): void {
        ['json' => $json] = fakeServiceAccountJson('boxcards-import');
        $path = storage_path('framework/testing/fcm-import-'.bin2hex(random_bytes(4)).'.json');
        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, $json);
        withEnvironment(['FCM_CREDENTIALS_PATH' => $path, 'FCM_ENABLED' => 'true']);

        try {
            Artisan::call('settings:import-env');
            $output = Artisan::output();
        } finally {
            unlink($path);
        }

        expect($output)->toMatch('/FCM_CREDENTIALS_PATH\s*\|\s*push\.fcm_service_account\s*\|\s*imported/')
            ->and($output)->not->toContain('PRIVATE KEY')->not->toContain($path)
            ->and(Setting::query()->where('group', 'push')->where('key', 'fcm_service_account')->value('is_encrypted'))->toBeTrue()
            ->and(config('push.enabled'))->toBeTrue()
            ->and(app(FcmClient::class)->credentials()['project_id'])->toBe('boxcards-import');
    });

    it('stores nothing for a value its setting refuses and exits with failure', function (): void {
        withEnvironment(['N8N_WEBHOOK_URL' => 'javascript:alert(1)', 'FCM_CREDENTIALS_PATH' => storage_path('framework/testing/no-such-key.json')]);

        $exit = Artisan::call('settings:import-env');

        expect($exit)->toBe(1)
            ->and(Artisan::output())->toMatch('/N8N_WEBHOOK_URL\s*\|\s*automation\.webhook_url\s*\|\s*invalid/')->toMatch('/FCM_CREDENTIALS_PATH\s*\|\s*push\.fcm_service_account\s*\|\s*invalid/')
            ->and(Setting::query()->whereIn('key', ['webhook_url', 'fcm_service_account'])->exists())->toBeFalse();
    });
});

describe('owner decision 11: everything but the bootstrap keys', function (): void {
    it('applies the saved values to the framework and project config keys', function (): void {
        adminWith(['settings.view', 'settings.manage', 'settings.manage_critical']);

        $this->putJson('/admin/api/settings/site', ['app_name' => 'Boxcards Shop', 'brand_name' => 'Boxcards', 'fallback_locale' => 'ar', 'faker_locale' => 'ar_SA'])->assertOk();
        $this->putJson('/admin/api/settings/session', ['app_url' => 'https://shop.boxcards.test', 'session_lifetime' => 45, 'session_encrypt' => true, 'session_secure_cookie' => true, 'session_path' => '/store', 'session_domain' => '.boxcards.test'])->assertOk();
        $this->putJson('/admin/api/settings/mail', ['mailer' => 'log', 'smtp_scheme' => 'smtps', 'smtp_host' => 'smtp.boxcards.test', 'smtp_port' => 465, 'smtp_username' => 'mailer', 'from_address' => 'orders@boxcards.test'])->assertOk();
        $this->putJson('/admin/api/settings/storage', ['filesystem_disk' => 'public', 'sitemap_disk' => 'public', 'media_base_url' => 'https://cdn.boxcards.test', 'aws_default_region' => 'eu-west-1', 'aws_use_path_style_endpoint' => true])->assertOk();
        $this->putJson('/admin/api/settings/security', ['risk_review_score' => 40, 'risk_block_score' => 80, 'risk_first_order_amount_base' => '100', 'password_timeout' => 900, 'mobile_access_token_ttl_minutes' => 20, 'mobile_refresh_token_ttl_days' => 14, 'mobile_2fa_challenge_ttl_seconds' => 120, 'guest_order_token_ttl_hours' => 12, 'api_token_prefix' => 'bxc_'])->assertOk();
        $this->putJson('/admin/api/settings/currency', ['max_age_minutes' => 1440, 'max_change_pct' => '10', 'oxr_base_url' => 'https://oxr.boxcards.test/api/', 'oxr_connect_timeout' => 2, 'oxr_timeout' => 9])->assertOk();
        $this->putJson('/admin/api/settings/payments', ['http_connect_timeout' => 3, 'http_timeout' => 30])->assertOk();
        $this->putJson('/admin/api/settings/affiliates', ['attribution_days' => 45])->assertOk();

        expect(config('app.name'))->toBe('Boxcards Shop')
            ->and(config('brand.name'))->toBe('Boxcards')
            ->and(config('app.fallback_locale'))->toBe('ar')
            ->and(config('app.faker_locale'))->toBe('ar_SA')
            ->and(config('app.url'))->toBe('https://shop.boxcards.test')
            ->and(config('session'))->toMatchArray(['lifetime' => 45, 'encrypt' => true, 'secure' => true, 'path' => '/store', 'domain' => '.boxcards.test', 'http_only' => true])
            ->and(config('mail.default'))->toBe('log')
            ->and(config('mail.mailers.smtp'))->toMatchArray(['scheme' => 'smtps', 'host' => 'smtp.boxcards.test', 'port' => 465, 'username' => 'mailer'])
            ->and(config('mail.from.address'))->toBe('orders@boxcards.test')
            ->and(config('filesystems.default'))->toBe('public')
            ->and(config('filesystems.disks.s3'))->toMatchArray(['region' => 'eu-west-1', 'use_path_style_endpoint' => true])
            ->and(config('seo.sitemaps.disk'))->toBe('public')
            ->and(config('catalog.media.base_url'))->toBe('https://cdn.boxcards.test')
            ->and(config('auth.password_timeout'))->toBe(900)
            ->and(config('mobile.auth'))->toMatchArray(['access_token_ttl_minutes' => 20, 'refresh_token_ttl_days' => 14, 'challenge_ttl_seconds' => 120])
            ->and(config('orders.guest_order_token_ttl_hours'))->toBe(12)
            ->and(config('sanctum.token_prefix'))->toBe('bxc_')
            ->and(config('services.open_exchange_rates'))->toMatchArray(['base_url' => 'https://oxr.boxcards.test/api/', 'connect_timeout' => 2, 'timeout' => 9])
            ->and(config('payments.http'))->toBe(['connect_timeout' => 3, 'timeout' => 30])
            ->and(config('affiliates.attribution_days'))->toBe(45);
    });

    it('derives the cookie, sender and store names and the address-based keys until they are set', function (): void {
        adminWith(['settings.manage', 'settings.manage_critical']);

        $this->putJson('/admin/api/settings/site', ['app_name' => 'Boxcards Shop'])->assertOk();
        $this->putJson('/admin/api/settings/session', ['app_url' => 'https://shop.boxcards.test/'])->assertOk();

        expect(config('session.cookie'))->toBe('boxcards-shop-session')
            ->and(config('mail.from.name'))->toBe('Boxcards Shop')
            ->and(config('automation.store_name'))->toBe('Boxcards Shop')
            ->and(config('mail.mailers.smtp.local_domain'))->toBe('shop.boxcards.test')
            ->and(config('filesystems.disks.public.url'))->toBe('https://shop.boxcards.test/storage');

        $this->putJson('/admin/api/settings/mail', ['from_name' => 'Boxcards Orders', 'ehlo_domain' => 'mx.boxcards.test'])->assertOk();
        $this->putJson('/admin/api/settings/session', ['session_cookie' => 'bx_session'])->assertOk();

        expect(config('mail.from.name'))->toBe('Boxcards Orders')
            ->and(config('mail.mailers.smtp.local_domain'))->toBe('mx.boxcards.test')
            ->and(config('session.cookie'))->toBe('bx_session')
            ->and(config('automation.store_name'))->toBe('Boxcards Shop');

        $this->putJson('/admin/api/settings/session', ['session_cookie' => null])->assertOk();
        expect(config('session.cookie'))->toBe('boxcards-shop-session');
    });

    it('keeps the mail password and the S3 secret write-only, encrypted and applied', function (): void {
        adminWith(['settings.view', 'settings.manage_critical']);
        $smtp = 'smtp-CANARY-decision11-3b9a';
        $s3 = 's3-CANARY-decision11-secret-7c1d';

        $this->putJson('/admin/api/settings/mail/secrets/smtp_password', ['value' => $smtp])->assertOk()->assertJsonPath('data.configured', true);
        $this->putJson('/admin/api/settings/storage/secrets/aws_secret_access_key', ['value' => $s3])->assertOk();

        expect(config('mail.mailers.smtp.password'))->toBe($smtp)
            ->and(config('filesystems.disks.s3.secret'))->toBe($s3)
            ->and(Setting::query()->whereIn('key', ['smtp_password', 'aws_secret_access_key'])->pluck('is_encrypted')->all())->toBe([true, true])
            ->and(Setting::query()->pluck('value')->implode(' '))->not->toContain($smtp)->not->toContain($s3)
            ->and(AuditLog::query()->get()->toJson())->not->toContain($smtp)->not->toContain($s3);

        foreach (['mail', 'storage'] as $group) {
            expect($this->get('/admin/settings/'.$group)->assertOk()->getContent())->not->toContain($smtp)->not->toContain($s3);
        }

        adminWith(['settings.manage']);
        $this->putJson('/admin/api/settings/mail/secrets/smtp_password', ['value' => 'another-password'])->assertForbidden();
    });

    it('resets the locale, mailers and disks a queue worker already built when a setting changes', function (): void {
        $overlay = app(SettingsOverlay::class);
        $overlay->watch();
        $mailer = app('mail.manager')->mailer('smtp');
        Storage::disk('public');

        foreach ([['site', 'locale', 'ar', SettingType::String], ['mail', 'smtp_host', 'smtp.worker.test', SettingType::String], ['session', 'app_url', 'https://worker.boxcards.test', SettingType::String]] as [$group, $key, $value, $type]) {
            (new Setting)->forceFill(['group' => $group, 'key' => $key, 'value' => $value, 'type' => $type, 'is_encrypted' => false])->save();
        }
        Cache::forever(SettingsOverlay::VERSION_KEY, 'saved-by-the-admin');

        event(new JobProcessing('redis', Mockery::mock(Job::class)->shouldIgnoreMissing()));

        $fresh = app('mail.manager')->mailer('smtp');
        expect(app()->getLocale())->toBe('ar')
            ->and($fresh)->not->toBe($mailer)
            ->and($fresh->getSymfonyTransport()->getStream()->getHost())->toBe('smtp.worker.test')
            ->and(Storage::disk('public')->url('a.png'))->toBe('https://worker.boxcards.test/storage/a.png')
            ->and(url('/x'))->toBe('https://worker.boxcards.test/x');

        $overlay->watch(false);
    });

    it('imports the framework keys once without printing a value', function (): void {
        $keys = array_values(array_filter(array_map(static fn ($definition): ?string => $definition->envKey, app(SettingDefinitions::class)->all())));
        withoutEnvironment($keys);
        withEnvironment([
            'APP_NAME' => 'Imported Store Name',
            'APP_URL' => 'https://imported.boxcards.test',
            'SESSION_LIFETIME' => '75',
            'MAIL_MAILER' => 'smtp',
            'MAIL_PASSWORD' => 'mail-CANARY-import-11aa',
            'MAIL_USERNAME' => 'null',
            'FILESYSTEM_DISK' => 'public',
            'AWS_SECRET_ACCESS_KEY' => 'aws-CANARY-import-secret-22bb',
            'MEDIA_BASE_URL' => 'https://cdn.imported.test',
        ]);

        try {
            $exit = Artisan::call('settings:import-env');
            $output = Artisan::output();
        } finally {
            withoutEnvironment($keys);
        }

        expect($exit)->toBe(0)
            ->and($output)->toMatch('/APP_URL\s*\|\s*session\.app_url\s*\|\s*imported/')->toMatch('/MAIL_PASSWORD\s*\|\s*mail\.smtp_password\s*\|\s*imported/')
            ->toMatch('/MAIL_USERNAME\s*\|\s*mail\.smtp_username\s*\|\s*empty/')
            ->not->toContain('Imported Store Name')->not->toContain('imported.boxcards.test')->not->toContain('mail-CANARY')->not->toContain('aws-CANARY')
            ->and(config('app.name'))->toBe('Imported Store Name')
            ->and(config('app.url'))->toBe('https://imported.boxcards.test')
            ->and(config('session.lifetime'))->toBe(75)
            ->and(config('session.cookie'))->toBe('imported-store-name-session')
            ->and(config('mail.default'))->toBe('smtp')
            ->and(config('mail.mailers.smtp.password'))->toBe('mail-CANARY-import-11aa')
            ->and(config('filesystems.default'))->toBe('public')
            ->and(config('filesystems.disks.s3.secret'))->toBe('aws-CANARY-import-secret-22bb')
            ->and(config('catalog.media.base_url'))->toBe('https://cdn.imported.test');
    });
});
