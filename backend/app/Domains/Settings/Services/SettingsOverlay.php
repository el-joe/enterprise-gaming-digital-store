<?php

namespace App\Domains\Settings\Services;

use App\Domains\Settings\DTOs\SettingDefinition;
use App\Domains\Settings\Models\Setting;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Applies the database settings that replaced `.env` keys over their config keys ("Settings from
 * the database"), so running code keeps reading `config()` while the settings table is the single
 * source of truth.
 *
 * - At boot every overlaid key with a stored value is written into config; a key without one
 *   keeps the built-in default of its config file (the baseline, captured before the first write).
 * - The stored rows are cached under a version key (`settings:overlay:version`) that every save
 *   replaces (SettingsService::set calls changed()). refresh() costs one cache read and reloads
 *   only when the version moved; long-running queue workers call it before each job, Octane-like
 *   servers before each request (SettingsServiceProvider).
 * - Fail soft: without a database, a settings table or a cache (fresh install, before migrations,
 *   artisan before migrate) nothing is applied and the config defaults stay. It never runs while
 *   config is being cached (`config:cache`, `optimize`), so no setting, and no decrypted secret,
 *   is ever written to `bootstrap/cache/config.php`.
 * - Secrets stay encrypted in the cache and are decrypted only into config. Values are never
 *   logged; an undecryptable secret (APP_KEY rotated without APP_PREVIOUS_KEYS) is logged by key
 *   name and left at its default.
 * - Derived keys (`admin_settings.derived`, owner decision 11) follow their source key while no
 *   setting of their own is stored: the session cookie name, the mail sender name and the n8n
 *   store name follow app.name; the public disk URL and the SMTP EHLO domain follow app.url.
 * - boot() and refresh() return the config keys whose value changed, so the provider can reset
 *   what was already built from them (translator locale, mailers, disks, the console URL).
 */
class SettingsOverlay
{
    public const string VERSION_KEY = 'settings:overlay:version';

    private const string PAYLOAD_KEY = 'settings:overlay:payload:';

    private const int PAYLOAD_SECONDS = 86400;

    /**
     * Commands that must never see database values in config: they cache or reset configuration,
     * or run before a database can exist.
     */
    public const array SKIPPED_COMMANDS = ['config:cache', 'config:clear', 'optimize', 'optimize:clear', 'package:discover', 'key:generate', 'clear-compiled'];

    /** @var array<string, mixed>|null config key => built-in default */
    private ?array $baseline = null;

    private ?string $appliedVersion = null;

    /** @var array<string, true> config keys currently holding a database value */
    private array $applied = [];

    private bool $watching = false;

    public function __construct(private SettingDefinitions $definitions, private Repository $config) {}

    /**
     * Apply the stored settings once at boot.
     */
    /**
     * @return list<string> the config keys whose value changed
     */
    public function boot(): array
    {
        $this->captureBaseline();

        return $this->refresh(force: true);
    }

    /**
     * Re-apply when a save moved the version since the last apply to the application config (one
     * cache read otherwise). With `$config` (an Octane request sandbox cloned from the application
     * config) the values are written there and the application config is left as it is.
     *
     * @return list<string> the config keys whose value changed
     */
    public function refresh(bool $force = false, ?Repository $config = null): array
    {
        $this->captureBaseline();

        try {
            $version = $this->currentVersion();

            if (! $force && $version === $this->appliedVersion) {
                return [];
            }

            $rows = $this->payload($version);
        } catch (Throwable) {
            return [];
        }

        $changed = $this->write($rows, $config ?? $this->config);

        if ($config === null) {
            $this->appliedVersion = $version;
        }

        return $changed;
    }

    /**
     * Called after every settings save: a new version for every process, and this one now.
     */
    public function changed(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, (string) Str::uuid());
        } catch (Throwable) {
            return;
        }

        $this->refresh();
    }

    /**
     * The built-in default of an overlaid config key (the config file value before any setting).
     */
    public function defaultFor(string $configKey): mixed
    {
        $this->captureBaseline();

        return array_key_exists($configKey, (array) $this->baseline) ? $this->baseline[$configKey] : $this->config->get($configKey);
    }

    /**
     * Whether queue workers re-check the version before each job (set by the provider in a worker).
     */
    public function watch(bool $watching = true): void
    {
        $this->watching = $watching;
    }

    public function watching(): bool
    {
        return $this->watching;
    }

    public function appliedVersion(): ?string
    {
        return $this->appliedVersion;
    }

    private function captureBaseline(): void
    {
        if ($this->baseline !== null) {
            return;
        }

        $this->baseline = [];

        foreach ($this->definitions->overlaid() as $definition) {
            $this->baseline[(string) $definition->configKey] = $this->config->get((string) $definition->configKey);
        }
    }

    private function currentVersion(): string
    {
        $version = Cache::get(self::VERSION_KEY);

        if (is_string($version) && $version !== '') {
            return $version;
        }

        Cache::add(self::VERSION_KEY, (string) Str::uuid(), null);

        return (string) Cache::get(self::VERSION_KEY);
    }

    /**
     * The stored rows of the overlaid settings, as stored (secrets still encrypted).
     *
     * @return array<string, array{value: string|null, type: string, is_encrypted: bool}>
     */
    private function payload(string $version): array
    {
        return Cache::remember(self::PAYLOAD_KEY.$version, self::PAYLOAD_SECONDS, function (): array {
            $definitions = $this->definitions->overlaid();
            $wanted = array_flip(array_map(static fn (SettingDefinition $definition): string => $definition->id(), $definitions));

            return Setting::query()
                ->whereIn('group', array_values(array_unique(array_map(static fn (SettingDefinition $definition): string => $definition->group, $definitions))))
                ->get(['group', 'key', 'value', 'type', 'is_encrypted'])
                ->filter(static fn (Setting $setting): bool => isset($wanted[$setting->group.'.'.$setting->key]))
                ->mapWithKeys(static fn (Setting $setting): array => [$setting->group.'.'.$setting->key => [
                    'value' => $setting->value,
                    'type' => $setting->type->value,
                    'is_encrypted' => $setting->is_encrypted,
                ]])
                ->all();
        });
    }

    /**
     * @param  array<string, array{value: string|null, type: string, is_encrypted: bool}>  $rows
     * @return list<string> the config keys whose value changed
     */
    private function write(array $rows, Repository $config): array
    {
        $keys = array_values(array_unique([...array_map(static fn (SettingDefinition $definition): string => (string) $definition->configKey, $this->definitions->overlaid()), ...array_keys($this->definitions->derived())]));
        $before = [];

        foreach ($keys as $key) {
            $before[$key] = $config->get($key);
        }

        $values = [];

        foreach ($this->definitions->overlaid() as $definition) {
            $row = $rows[$definition->id()] ?? null;

            if ($row === null || $row['value'] === null) {
                continue;
            }

            try {
                $values[(string) $definition->configKey] = SettingsService::decodeStored($row);
            } catch (Throwable) {
                Log::warning('settings.overlay.unreadable', ['setting' => $definition->id()]);
            }
        }

        foreach ($this->definitions->overlaid() as $definition) {
            $key = (string) $definition->configKey;

            if (array_key_exists($key, $values)) {
                $config->set($key, $values[$key]);
                $this->applied[$key] = true;
            } elseif (isset($this->applied[$key])) {
                $config->set($key, $this->baseline[$key] ?? null);
                unset($this->applied[$key]);
            }
        }

        foreach ($this->definitions->derived() as $key => $rule) {
            if (array_key_exists($key, $values)) {
                continue;
            }

            $derived = self::derive((string) $rule['as'], $config->get((string) $rule['from']));

            if ($derived !== null) {
                $config->set($key, $derived);
            }
        }

        return array_values(array_filter($keys, static fn (string $key): bool => $config->get($key) !== $before[$key]));
    }

    /**
     * The value a derived config key takes from its source, or null to leave it as it is.
     */
    public static function derive(string $as, mixed $source): ?string
    {
        if (! is_string($source) || trim($source) === '') {
            return null;
        }

        return match ($as) {
            'same' => $source,
            'host' => is_string($host = parse_url($source, PHP_URL_HOST)) && $host !== '' ? $host : null,
            'storage_url' => rtrim($source, '/').'/storage',
            'session_cookie' => Str::slug($source).'-session',
            default => null,
        };
    }
}
