<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyChangeRefused;
use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyMissing;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Settings\DTOs\SettingDefinition;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\EnvSettingsImporter;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The only writer of the code-vault keys (owner decision 11, SECURITY.md 5). They are the settings
 * digital_codes.code_keys and digital_codes.blind_index_key, encrypted with APP_KEY (envelope
 * encryption: APP_KEY wraps the data keys, which encrypt and index the codes), and
 * digital_codes.active_kid. SettingsOverlay applies them over `fulfillment.*`, where
 * DigitalCodeKeyring reads them. They are never shown or edited on a screen.
 *
 * - generate(): first install only. Refused while any vault key is configured, while codes exist
 *   (they need their original keys: import them) and while DIGITAL_CODE_KEYS is still in the
 *   environment (`settings:import-env` copies it).
 * - rotate(): adds a new random key, makes it active and re-encrypts the codes on older keys in
 *   chunks (DigitalCodeKeyRotator). Old keys stay in the keyring, so a code not yet re-encrypted
 *   (or with `reencrypt: false`) is still read with its own kid.
 * - rebuildBlindIndex(): the only way to change the blind index key once it is set: a new key,
 *   every code_hash recomputed (two passes around the switch, so rows written meanwhile are
 *   covered), then the key saved.
 * - import(): `settings:import-env` stores the environment values exactly, only where nothing is
 *   stored yet; never over a stored key, even with --force.
 *
 * Every change is audited by SettingsService (encrypted values as changed only) and recorded as a
 * security event with key ids and counts, never key material. Changes hold one lock.
 */
class DigitalCodeKeyManager
{
    public const string GROUP = 'digital_codes';

    private const string LOCK = 'digital-codes:keys';

    private const int LOCK_SECONDS = 3600;

    public function __construct(
        private SettingsService $settings,
        private SecurityEventRecorder $securityEvents,
        private Container $container,
    ) {}

    /**
     * What the admin may see: whether the vault is usable, the active key id, the number of keys
     * and whether the keys come from the database. Never a key.
     *
     * @return array{configured: bool, active_kid: int|null, key_count: int, blind_index: bool, stored: bool}
     */
    public function status(): array
    {
        try {
            $kids = array_keys(DigitalCodeKeyring::parse((string) config('fulfillment.code_keys')));
        } catch (DigitalCodeKeyMissing) {
            $kids = [];
        }

        $active = config('fulfillment.active_kid');
        $activeKid = is_numeric($active) && in_array((int) $active, $kids, true) ? (int) $active : null;
        $blindIndex = is_string($key = config('fulfillment.blind_index_key')) && strlen($key) >= DigitalCodeKeyring::MIN_BLIND_INDEX_LENGTH;

        return [
            'configured' => $activeKid !== null && $blindIndex,
            'active_kid' => $activeKid,
            'key_count' => count($kids),
            'blind_index' => $blindIndex,
            'stored' => $this->settings->has(self::GROUP, 'code_keys') && $this->settings->has(self::GROUP, 'active_kid') && $this->settings->has(self::GROUP, 'blind_index_key'),
        ];
    }

    /**
     * Generate the first keyring and blind index key.
     *
     * @throws DigitalCodeKeyChangeRefused
     */
    public function generate(string $via = 'command'): void
    {
        $this->locked(function () use ($via): void {
            $this->assertGeneratable();

            $this->store('code_keys', '1:'.base64_encode(random_bytes(32)), SettingType::String, true);
            $this->store('active_kid', 1, SettingType::Int, false);
            $this->store('blind_index_key', bin2hex(random_bytes(32)), SettingType::String, true);

            $this->securityEvents->record('digital_codes.keys_generated', SecurityEventSeverity::Warning, context: ['via' => $via, 'active_kid' => 1, 'key_count' => 1]);
        });
    }

    /**
     * Generate the keys when the vault has none and nothing forbids it (the seeder); false when skipped.
     */
    public function generateIfAbsent(): bool
    {
        try {
            $this->generate('seeder');
        } catch (DigitalCodeKeyChangeRefused) {
            return false;
        }

        return true;
    }

    /**
     * Add a new key, make it active and (by default) re-encrypt the codes on older keys.
     *
     * @return array{kid: int, previous_kid: int, key_count: int, reencrypted: int|null}
     *
     * @throws DigitalCodeKeyChangeRefused
     */
    public function rotate(bool $reencrypt = true, int $chunk = 200): array
    {
        return $this->locked(function () use ($reencrypt, $chunk): array {
            if (! $this->status()['configured']) {
                throw new DigitalCodeKeyChangeRefused('The code vault keys are not configured: run codes:generate-keys or settings:import-env first.');
            }

            $keyring = (string) config('fulfillment.code_keys');
            $previous = (int) config('fulfillment.active_kid');
            $kid = max(array_keys(DigitalCodeKeyring::parse($keyring))) + 1;
            $keys = rtrim($keyring, ', ').','.$kid.':'.base64_encode(random_bytes(32));

            $this->store('code_keys', $keys, SettingType::String, true);
            $this->store('active_kid', $kid, SettingType::Int, false);

            if ((int) config('fulfillment.active_kid') !== $kid) {
                throw new DigitalCodeKeyChangeRefused('The new key was saved but is not applied in this process (settings cache unavailable); run digital-codes:rotate-keys to re-encrypt.');
            }

            $count = count(DigitalCodeKeyring::parse($keys));
            $this->securityEvents->record('digital_codes.key_rotated', SecurityEventSeverity::Warning, context: ['active_kid' => $kid, 'previous_kid' => $previous, 'key_count' => $count, 'reencrypt' => $reencrypt]);

            $reencrypted = $reencrypt ? $this->container->make(DigitalCodeKeyRotator::class)->rotate($chunk) : null;

            return ['kid' => $kid, 'previous_kid' => $previous, 'key_count' => $count, 'reencrypted' => $reencrypted];
        });
    }

    /**
     * Replace the blind index key and recompute every code_hash with it.
     *
     * @return int the number of codes re-indexed
     *
     * @throws DigitalCodeKeyChangeRefused
     */
    public function rebuildBlindIndex(int $chunk = 200): int
    {
        return $this->locked(function () use ($chunk): int {
            $status = $this->status();

            if ($status['active_kid'] === null) {
                throw new DigitalCodeKeyChangeRefused('The code keyring is not configured: the codes cannot be read to re-index them.');
            }

            $vault = $this->container->make(DigitalCodeVault::class);
            $key = bin2hex(random_bytes(32));
            $snapshot = (int) DigitalCode::query()->max('id');

            $count = $this->rehash($vault, $key, $chunk, static fn ($query) => $query->where('id', '<=', $snapshot));
            $this->store('blind_index_key', $key, SettingType::String, true);
            $count += $this->rehash($vault, $key, $chunk, static fn ($query) => $query->where('id', '>', $snapshot));

            $this->securityEvents->record('digital_codes.blind_index_rebuilt', SecurityEventSeverity::Warning, context: ['codes' => $count, 'replaced' => $status['blind_index']]);

            return $count;
        });
    }

    /**
     * Store one environment value of a vault key exactly (`settings:import-env`). Never overwrites
     * a stored key, `--force` or not: a different key would make the stored codes unreadable. A
     * stored key that differs from the environment is reported as a mismatch (compared in memory,
     * never printed).
     *
     * @return string an EnvSettingsImporter status
     */
    public function import(SettingDefinition $definition, string $raw): string
    {
        $value = $this->validImportValue($definition->key, $raw);

        if ($this->settings->has(self::GROUP, $definition->key)) {
            return $value !== null && $this->settings->get(self::GROUP, $definition->key) === $value ? EnvSettingsImporter::SKIPPED : EnvSettingsImporter::MISMATCH;
        }

        if ($value === null) {
            return EnvSettingsImporter::INVALID;
        }

        $this->locked(function () use ($definition, $value): void {
            $this->store($definition->key, $value, $definition->type, $definition->encrypted);
            $this->securityEvents->record('digital_codes.key_imported', SecurityEventSeverity::Warning, context: ['setting' => $definition->id()]);
        });

        return EnvSettingsImporter::IMPORTED;
    }

    private function validImportValue(string $key, string $raw): string|int|null
    {
        return match ($key) {
            'code_keys' => $this->validKeyring($raw) ? $raw : null,
            'active_kid' => ctype_digit(trim($raw)) ? (int) trim($raw) : null,
            'blind_index_key' => strlen($raw) >= DigitalCodeKeyring::MIN_BLIND_INDEX_LENGTH ? $raw : null,
            default => null,
        };
    }

    private function validKeyring(string $keyring): bool
    {
        try {
            return DigitalCodeKeyring::parse($keyring) !== [];
        } catch (DigitalCodeKeyMissing) {
            return false;
        }
    }

    /**
     * @throws DigitalCodeKeyChangeRefused
     */
    private function assertGeneratable(): void
    {
        foreach (['code_keys', 'active_kid', 'blind_index_key'] as $key) {
            if ($this->settings->has(self::GROUP, $key) || ! in_array(config("fulfillment.{$key}"), [null, ''], true)) {
                throw new DigitalCodeKeyChangeRefused('The code vault keys are already configured; use codes:rotate-key or codes:rebuild-blind-index.');
            }
        }

        if (EnvSettingsImporter::environmentHas('DIGITAL_CODE_KEYS')) {
            throw new DigitalCodeKeyChangeRefused('DIGITAL_CODE_KEYS is still in the environment: run settings:import-env to keep the existing keys.');
        }

        if (DigitalCode::query()->exists()) {
            throw new DigitalCodeKeyChangeRefused('Digital codes exist: they need their original keys. Restore the keys and run settings:import-env.');
        }
    }

    /**
     * @param  callable(Builder<DigitalCode>): mixed  $scope
     */
    private function rehash(DigitalCodeVault $vault, string $key, int $chunk, callable $scope): int
    {
        $count = 0;

        $scope(DigitalCode::query()->select('id'))->chunkById($chunk, function ($rows) use ($vault, $key, &$count): void {
            foreach ($rows as $row) {
                DB::transaction(function () use ($vault, $key, $row, &$count): void {
                    $code = DigitalCode::query()->lockForUpdate()->find($row->id);

                    if ($code !== null) {
                        $vault->rehash($code, $key);
                        $count++;
                    }
                });
            }
        });

        return $count;
    }

    private function store(string $key, string|int $value, SettingType $type, bool $encrypted): void
    {
        $this->settings->set(self::GROUP, $key, $value, $type, encrypted: $encrypted);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     *
     * @throws DigitalCodeKeyChangeRefused
     */
    private function locked(callable $callback): mixed
    {
        $lock = Cache::lock(self::LOCK, self::LOCK_SECONDS);

        if (! $lock->get()) {
            throw new DigitalCodeKeyChangeRefused('Another code vault key change is running.');
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
