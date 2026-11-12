<?php

namespace App\Domains\Settings\Services;

use App\Domains\Settings\DTOs\SettingDefinition;
use App\Domains\Settings\Enums\AppConfigSetting;
use App\Domains\Settings\Enums\SettingType;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Validator;

/**
 * Copies the current `.env` values of the keys that moved to the database into the settings, once
 * (`php artisan settings:import-env`). Only this importer reads those keys from the environment.
 *
 * Per key it reports `imported`, `skipped` (a value is already stored and `force` is off),
 * `empty` (not set) or `invalid` (refused by the setting's own validation; nothing stored). A row
 * still holding the seeded built-in default and never saved by staff counts as not set, so a
 * seeded database still imports. Values are never returned, printed or logged: secrets are stored
 * encrypted and audited only as changed. `env_file` keys (FCM_CREDENTIALS_PATH) name a file whose
 * content is imported.
 */
class EnvSettingsImporter
{
    public const string IMPORTED = 'imported';

    public const string SKIPPED = 'skipped';

    public const string EMPTY = 'empty';

    public const string INVALID = 'invalid';

    public function __construct(
        private SettingDefinitions $definitions,
        private SettingsService $settings,
        private AdminSettingsService $admin,
        private SettingsOverlay $overlay,
    ) {}

    /**
     * @return list<array{env: string, setting: string, status: string}>
     */
    public function import(bool $force = false): array
    {
        $results = [];

        foreach ($this->definitions->all() as $definition) {
            if ($definition->envKey === null) {
                continue;
            }

            $results[] = ['env' => $definition->envKey, 'setting' => $definition->id(), 'status' => $this->importOne($definition, $force)];
        }

        return $results;
    }

    private function importOne(SettingDefinition $definition, bool $force): string
    {
        $raw = $this->environmentValue($definition);

        if ($raw === null) {
            return self::EMPTY;
        }

        if (! $force && $this->alreadySet($definition)) {
            return self::SKIPPED;
        }

        $value = $this->parse($definition, $raw);

        if ($value === null) {
            return self::INVALID;
        }

        $this->settings->set($definition->group, $definition->key, $value, $definition->type, encrypted: $definition->encrypted);

        return self::IMPORTED;
    }

    private function environmentValue(SettingDefinition $definition): ?string
    {
        $value = Env::get((string) $definition->envKey);

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        if (! is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        if (! $definition->envFile) {
            return $value;
        }

        if (! is_file($value) || ! is_readable($value)) {
            return '';
        }

        return trim((string) file_get_contents($value));
    }

    /**
     * Stored by staff, or different from the built-in default (a seeded default is not "set").
     */
    private function alreadySet(SettingDefinition $definition): bool
    {
        $row = $this->settings->stored($definition->group, $definition->key);

        if ($row === null || $row['value'] === null) {
            return false;
        }

        if ($definition->encrypted || $row['updated_by'] !== null) {
            return true;
        }

        return $this->settings->get($definition->group, $definition->key) !== $this->builtInDefault($definition);
    }

    private function builtInDefault(SettingDefinition $definition): mixed
    {
        if ($definition->configKey !== null) {
            return $this->overlay->defaultFor($definition->configKey);
        }

        return $definition->group === AppConfigSetting::GROUP ? AppConfigSetting::from($definition->key)->default() : null;
    }

    /**
     * The typed value, or null when the setting's validation refuses it.
     */
    private function parse(SettingDefinition $definition, string $raw): mixed
    {
        if ($raw === '') {
            return null;
        }

        if ($definition->group === AppConfigSetting::GROUP) {
            $parsed = AppConfigSetting::from($definition->key)->parse($raw);

            return $parsed['error'] === null ? $parsed['value'] : null;
        }

        $value = match ($definition->type) {
            SettingType::Bool => filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            default => $raw,
        };

        if ($value === null) {
            return null;
        }

        if (Validator::make(['value' => $value], ['value' => $this->admin->rulesFor($definition->screen, $definition->key)])->fails()) {
            return null;
        }

        return match ($definition->type) {
            SettingType::Int => (int) $value,
            default => $value,
        };
    }
}
