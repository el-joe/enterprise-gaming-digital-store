<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Audit\Models\SecurityEvent;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyMissing;
use App\Domains\Fulfillment\Exceptions\DuplicateDigitalCode;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Services\DigitalCodeKeyManager;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Services\SettingDefinitions;
use App\Domains\Settings\Services\SettingsOverlay;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use Database\Seeders\SettingsSeeder;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\Support\TestConfiguration;

/*
| Owner decision 11: the code-vault keys (DIGITAL_CODE_*) are database settings, envelope-encrypted
| with APP_KEY, applied over config/fulfillment.php by SettingsOverlay, never shown or edited on a
| screen and changed only by artisan commands (DigitalCodeKeyManager). The import copies the former
| `.env` values exactly, so codes encrypted before it still decrypt and still match their blind index.
*/

const VAULT_TEST_CODE = 'VAULT-KEEP-1234-ABCD';

beforeEach(function (): void {
    // Whatever the machine's own .env holds, every key the import reads starts unset.
    $this->importedKeys = array_values(array_filter(array_map(static fn ($definition): ?string => $definition->envKey, app(SettingDefinitions::class)->all())));
    withoutEnvironment($this->importedKeys);
});

afterEach(fn () => withoutEnvironment($this->importedKeys));

function vaultKeyEnvironment(): array
{
    return [
        'DIGITAL_CODE_KEYS' => TestConfiguration::CODE_KEYS,
        'DIGITAL_CODE_ACTIVE_KID' => TestConfiguration::ACTIVE_KID,
        'DIGITAL_CODE_BLIND_INDEX_KEY' => TestConfiguration::BLIND_INDEX_KEY,
    ];
}

/**
 * The release that stopped reading DIGITAL_CODE_* from the environment, before the import ran.
 */
function withoutVaultKeysInConfig(): void
{
    config(['fulfillment.code_keys' => null, 'fulfillment.active_kid' => null, 'fulfillment.blind_index_key' => null]);
}

/**
 * Every value that must never appear in output, a page, the audit or a security event.
 *
 * @return list<string>
 */
function vaultKeyMaterial(): array
{
    $material = [TestConfiguration::BLIND_INDEX_KEY];

    foreach (explode(',', TestConfiguration::CODE_KEYS) as $pair) {
        $material[] = explode(':', $pair, 2)[1];
    }

    return $material;
}

function expectNoVaultKeyMaterial(string $haystack): void
{
    foreach (vaultKeyMaterial() as $secret) {
        expect($haystack)->not->toContain($secret);
    }
}

describe('import', function (): void {
    it('copies the .env keys exactly: codes encrypted before the import decrypt after it', function (): void {
        $before = app(DigitalCodeVault::class)->store(new DeliverableItem(VAULT_TEST_CODE, '4321', 'https://redeem.example.com/v'), DigitalCodeSource::Stock);

        withoutVaultKeysInConfig();
        expect(fn () => app(DigitalCodeVault::class)->decrypt($before))->toThrow(DigitalCodeKeyMissing::class);

        withEnvironment(vaultKeyEnvironment());
        $exit = Artisan::call('settings:import-env');
        $output = Artisan::output();

        expect($exit)->toBe(0)
            ->and($output)->toMatch('/DIGITAL_CODE_KEYS\s*\|\s*digital_codes\.code_keys\s*\|\s*imported/')
            ->toMatch('/DIGITAL_CODE_ACTIVE_KID\s*\|\s*digital_codes\.active_kid\s*\|\s*imported/')
            ->toMatch('/DIGITAL_CODE_BLIND_INDEX_KEY\s*\|\s*digital_codes\.blind_index_key\s*\|\s*imported/');
        expectNoVaultKeyMaterial($output);

        foreach (['code_keys' => TestConfiguration::CODE_KEYS, 'blind_index_key' => TestConfiguration::BLIND_INDEX_KEY] as $key => $value) {
            $row = Setting::query()->where('group', 'digital_codes')->where('key', $key)->firstOrFail();
            expect($row->is_encrypted)->toBeTrue()->and(Crypt::decryptString($row->getRawOriginal('value')))->toBe($value);
            expectNoVaultKeyMaterial($row->getRawOriginal('value'));
        }

        $plain = app(DigitalCodeVault::class)->decrypt($before);

        expect(config('fulfillment.code_keys'))->toBe(TestConfiguration::CODE_KEYS)
            ->and(config('fulfillment.active_kid'))->toBe(1)
            ->and(config('fulfillment.blind_index_key'))->toBe(TestConfiguration::BLIND_INDEX_KEY)
            ->and($plain->code)->toBe(VAULT_TEST_CODE)
            ->and($plain->pin)->toBe('4321')
            ->and($plain->redemptionUrl)->toBe('https://redeem.example.com/v')
            ->and(fn () => app(DigitalCodeVault::class)->store(new DeliverableItem(strtolower(VAULT_TEST_CODE)), DigitalCodeSource::Stock))->toThrow(DuplicateDigitalCode::class);

        // A fresh process with no keys in its config files reads them from the database.
        $config = new Repository(['fulfillment' => ['code_keys' => null, 'active_kid' => null, 'blind_index_key' => null]]);
        (new SettingsOverlay(app(SettingDefinitions::class), $config))->boot();
        expect($config->get('fulfillment.code_keys'))->toBe(TestConfiguration::CODE_KEYS)
            ->and($config->get('fulfillment.blind_index_key'))->toBe(TestConfiguration::BLIND_INDEX_KEY);

        $audit = AuditLog::query()->where('action', 'settings.updated')->get();
        $events = SecurityEvent::query()->where('type', 'digital_codes.key_imported')->get();
        expect($events)->toHaveCount(3)
            ->and($audit->where('after', ['value' => 'changed']))->toHaveCount(2);
        expectNoVaultKeyMaterial($audit->toJson().$events->toJson());
    });

    it('never overwrites a stored vault key, even with --force, and reports a different one', function (): void {
        withEnvironment(vaultKeyEnvironment());
        Artisan::call('settings:import-env');

        Artisan::call('settings:import-env', ['--force' => true]);
        expect(Artisan::output())->toMatch('/DIGITAL_CODE_KEYS\s*\|\s*digital_codes\.code_keys\s*\|\s*skipped/');

        withEnvironment(['DIGITAL_CODE_BLIND_INDEX_KEY' => str_repeat('f', 64)]);
        $exit = Artisan::call('settings:import-env', ['--force' => true]);
        $output = Artisan::output();

        expect($exit)->toBe(1)
            ->and($output)->toMatch('/DIGITAL_CODE_BLIND_INDEX_KEY\s*\|\s*digital_codes\.blind_index_key\s*\|\s*mismatch/')
            ->not->toContain(str_repeat('f', 64))
            ->and(app(SettingsService::class)->get('digital_codes', 'blind_index_key'))->toBe(TestConfiguration::BLIND_INDEX_KEY);
    });

    it('refuses an invalid keyring and stores nothing', function (): void {
        withEnvironment(['DIGITAL_CODE_KEYS' => '1:not-a-32-byte-key', 'DIGITAL_CODE_ACTIVE_KID' => 'one', 'DIGITAL_CODE_BLIND_INDEX_KEY' => 'short']);

        expect(Artisan::call('settings:import-env'))->toBe(1)
            ->and(Artisan::output())->toMatch('/DIGITAL_CODE_KEYS\s*\|\s*digital_codes\.code_keys\s*\|\s*invalid/')
            ->toMatch('/DIGITAL_CODE_ACTIVE_KID\s*\|\s*digital_codes\.active_kid\s*\|\s*invalid/')
            ->toMatch('/DIGITAL_CODE_BLIND_INDEX_KEY\s*\|\s*digital_codes\.blind_index_key\s*\|\s*invalid/')
            ->and(Setting::query()->where('group', 'digital_codes')->exists())->toBeFalse();
    });
});

describe('admin screen', function (): void {
    it('shows only the state, active key id and key count, to holders of both critical permissions', function (): void {
        withEnvironment(vaultKeyEnvironment());
        Artisan::call('settings:import-env');

        adminWith(['settings.view', 'settings.manage_critical']);
        $this->get('/admin/settings/digital_codes')->assertOk()
            ->assertSee(__('admin.settings.vault.needs', ['permissions' => 'settings.manage_critical, digital_codes.reveal']))
            ->assertDontSee(__('admin.settings.vault.active_kid'));

        adminWith(['settings.view', 'settings.manage_critical', 'digital_codes.reveal']);

        foreach (['en', 'ar'] as $locale) {
            $page = $this->withSession(['admin.locale' => $locale])->get('/admin/settings/digital_codes')->assertOk()
                ->assertSee(__('admin.settings.vault.active_kid', locale: $locale))
                ->assertSee(__('admin.settings.vault.stored_yes', locale: $locale))
                ->assertSee('php artisan codes:rotate-key');
            expect($page->getContent())->toMatch('~'.preg_quote(__('admin.settings.vault.key_count', locale: $locale), '~').'</dt>\s*<dd[^>]*>2</dd>~');
            expectNoVaultKeyMaterial($page->getContent());
            assertCspSafeHtml($page);
        }

        foreach (array_keys((array) config('admin_settings.groups')) as $group) {
            expectNoVaultKeyMaterial($this->get('/admin/settings/'.$group)->getContent());
        }
    });

    it('has no way to save or replace a vault key from the admin', function (): void {
        adminWith(['settings.view', 'settings.manage', 'settings.manage_critical', 'digital_codes.reveal']);

        $this->putJson('/admin/api/settings/digital_codes', ['code_keys' => '1:'.base64_encode(random_bytes(32)), 'active_kid' => 9])->assertStatus(409);
        $this->putJson('/admin/api/settings/digital_codes/secrets/code_keys', ['value' => '1:'.base64_encode(random_bytes(32))])->assertNotFound();
        $this->putJson('/admin/api/settings/digital_codes/secrets/blind_index_key', ['value' => str_repeat('a', 64)])->assertNotFound();

        expect(Setting::query()->where('group', 'digital_codes')->exists())->toBeFalse()
            ->and(config('fulfillment.active_kid'))->toBe(TestConfiguration::ACTIVE_KID);

        adminWith(['settings.view', 'settings.manage']);
        $this->putJson('/admin/api/settings/digital_codes', [])->assertForbidden();
    });
});

describe('rotation', function (): void {
    it('adds a new active key and re-encrypts older codes in batches', function (): void {
        $code = app(DigitalCodeVault::class)->store(new DeliverableItem(VAULT_TEST_CODE, '4321'), DigitalCodeSource::Stock);
        $hash = DB::table('digital_codes')->where('id', $code->id)->value('code_hash');

        $exit = Artisan::call('codes:rotate-key', ['--chunk' => 1]);
        $output = Artisan::output();

        $code->refresh();
        expect($exit)->toBe(0)
            ->and($output)->toContain('Key 3 is active (was 1)')->toContain('Re-encrypted 1 codes')
            ->and(config('fulfillment.active_kid'))->toBe(3)
            ->and(app(DigitalCodeKeyManager::class)->status())->toMatchArray(['configured' => true, 'active_kid' => 3, 'key_count' => 3])
            ->and($code->key_version)->toBe(3)
            ->and(DB::table('digital_codes')->where('id', $code->id)->value('code_hash'))->toBe($hash)
            ->and(app(DigitalCodeVault::class)->decrypt($code)->code)->toBe(VAULT_TEST_CODE)
            ->and(app(SettingsService::class)->get('digital_codes', 'code_keys'))->toStartWith(TestConfiguration::CODE_KEYS.',3:')
            ->and(SecurityEvent::query()->where('type', 'digital_codes.key_rotated')->sole()->context)->toMatchArray(['active_kid' => 3, 'previous_kid' => 1, 'key_count' => 3]);
        expectNoVaultKeyMaterial($output);
        expect($output)->not->toContain((string) explode('3:', (string) app(SettingsService::class)->get('digital_codes', 'code_keys'))[1]);
    });

    it('can leave older codes on their own key, which stays readable', function (): void {
        $code = app(DigitalCodeVault::class)->store(new DeliverableItem(VAULT_TEST_CODE), DigitalCodeSource::Stock);

        expect(Artisan::call('codes:rotate-key', ['--no-reencrypt' => true]))->toBe(0);

        $newer = app(DigitalCodeVault::class)->store(new DeliverableItem('VAULT-NEW-0001'), DigitalCodeSource::Stock);

        expect($code->fresh()->key_version)->toBe(1)
            ->and($newer->key_version)->toBe(3)
            ->and(app(DigitalCodeVault::class)->decrypt($code->fresh())->code)->toBe(VAULT_TEST_CODE)
            ->and(app(DigitalCodeVault::class)->decrypt($newer)->code)->toBe('VAULT-NEW-0001');

        Artisan::call('digital-codes:rotate-keys');
        expect($code->fresh()->key_version)->toBe(3);
    });

    it('refuses to rotate without a keyring', function (): void {
        withoutVaultKeysInConfig();

        expect(Artisan::call('codes:rotate-key'))->toBe(1)
            ->and(Setting::query()->where('group', 'digital_codes')->exists())->toBeFalse();
    });
});

describe('blind index', function (): void {
    it('changes only through the rebuild, which re-indexes every code', function (): void {
        $codes = collect(['VAULT-IDX-0001', 'VAULT-IDX-0002', 'VAULT-IDX-0003'])
            ->map(fn (string $value) => app(DigitalCodeVault::class)->store(new DeliverableItem($value), DigitalCodeSource::Stock));
        $before = DB::table('digital_codes')->pluck('code_hash', 'id');

        expect(Artisan::call('codes:rebuild-blind-index', ['--no-interaction' => true]))->toBe(1)
            ->and(DB::table('digital_codes')->pluck('code_hash', 'id')->all())->toBe($before->all());

        expect(Artisan::call('codes:rebuild-blind-index', ['--force' => true, '--chunk' => 2]))->toBe(0)
            ->and(Artisan::output())->toContain('3 codes re-indexed');

        $after = DB::table('digital_codes')->pluck('code_hash', 'id');
        $key = (string) app(SettingsService::class)->get('digital_codes', 'blind_index_key');

        expect($key)->not->toBe(TestConfiguration::BLIND_INDEX_KEY)
            ->and(config('fulfillment.blind_index_key'))->toBe($key)
            ->and(Setting::query()->where('group', 'digital_codes')->where('key', 'blind_index_key')->value('is_encrypted'))->toBeTrue()
            ->and(array_intersect($after->all(), $before->all()))->toBe([])
            ->and($after->get($codes[1]->id))->toBe(app(DigitalCodeVault::class)->hashFor('VAULT-IDX-0002'))
            ->and(fn () => app(DigitalCodeVault::class)->store(new DeliverableItem('vault idx 0003'), DigitalCodeSource::Stock))->toThrow(DuplicateDigitalCode::class)
            ->and(SecurityEvent::query()->where('type', 'digital_codes.blind_index_rebuilt')->sole()->context)->toMatchArray(['codes' => 3, 'replaced' => true]);
    });
});

describe('first install', function (): void {
    it('generates the keys once when none exist, encrypted, and refuses afterwards', function (): void {
        withoutVaultKeysInConfig();

        expect(Artisan::call('codes:generate-keys'))->toBe(0);
        $output = Artisan::output();

        $status = app(DigitalCodeKeyManager::class)->status();
        expect($status)->toBe(['configured' => true, 'active_kid' => 1, 'key_count' => 1, 'blind_index' => true, 'stored' => true])
            ->and($output)->not->toContain((string) config('fulfillment.blind_index_key'))
            ->and(Setting::query()->where('group', 'digital_codes')->where('is_encrypted', true)->count())->toBe(2)
            ->and(SecurityEvent::query()->where('type', 'digital_codes.keys_generated')->count())->toBe(1);

        $code = app(DigitalCodeVault::class)->store(new DeliverableItem(VAULT_TEST_CODE), DigitalCodeSource::Stock);
        expect(app(DigitalCodeVault::class)->decrypt($code)->code)->toBe(VAULT_TEST_CODE)
            ->and(Artisan::call('codes:generate-keys'))->toBe(1)
            ->and(Artisan::output())->toContain('already configured');
    });

    it('refuses while codes exist or the .env keys are not imported yet', function (): void {
        app(DigitalCodeVault::class)->store(new DeliverableItem(VAULT_TEST_CODE), DigitalCodeSource::Stock);
        withoutVaultKeysInConfig();

        expect(Artisan::call('codes:generate-keys'))->toBe(1)
            ->and(Artisan::output())->toContain('Digital codes exist');

        DigitalCode::query()->delete();
        withEnvironment(['DIGITAL_CODE_KEYS' => TestConfiguration::CODE_KEYS]);

        expect(Artisan::call('codes:generate-keys'))->toBe(1)
            ->and(Artisan::output())->toContain('settings:import-env')
            ->and(Setting::query()->where('group', 'digital_codes')->exists())->toBeFalse();
    });

    it('is generated by the seeder on a first install only', function (): void {
        $this->seed(SettingsSeeder::class);
        expect(Setting::query()->where('group', 'digital_codes')->exists())->toBeFalse();

        withoutVaultKeysInConfig();
        $this->seed(SettingsSeeder::class);

        expect(app(DigitalCodeKeyManager::class)->status()['configured'])->toBeTrue()
            ->and(Setting::query()->where('group', 'digital_codes')->count())->toBe(3);
    });
});
