<?php

namespace App\Domains\Settings\Services;

use App\Domains\Settings\DTOs\SettingDefinition;
use App\Domains\Settings\Enums\AppConfigSetting;
use App\Domains\Settings\Enums\SettingType;

/**
 * The settings that live in the database instead of `.env` (or over a config default), read from
 * `config/admin_settings.php`: fields and secrets with `config` (applied by SettingsOverlay) or
 * `env` (copied once by `settings:import-env`), plus the public app config keys of the mobile
 * group's `env` map. One definition serves the screens, the overlay and the import.
 */
class SettingDefinitions
{
    /**
     * @return list<SettingDefinition>
     */
    public function all(): array
    {
        $definitions = [];

        foreach ((array) config('admin_settings.groups') as $screen => $group) {
            $store = $group['store'] ?? null;

            if (! is_string($store)) {
                continue;
            }

            $permission = (string) $group['permission'];

            foreach ((array) ($group['fields'] ?? []) as $key => $field) {
                if (isset($field['config']) || isset($field['env'])) {
                    $definitions[] = new SettingDefinition(
                        screen: (string) $screen,
                        group: $store,
                        key: (string) $key,
                        type: self::typeOf((string) $field['type']),
                        encrypted: false,
                        permission: $permission,
                        configKey: $field['config'] ?? null,
                        envKey: $field['env'] ?? null,
                        format: $field['format'] ?? null,
                    );
                }
            }

            foreach ((array) ($group['secrets'] ?? []) as $key => $secret) {
                if (isset($secret['config']) || isset($secret['env'])) {
                    $definitions[] = new SettingDefinition(
                        screen: (string) $screen,
                        group: $store,
                        key: (string) $key,
                        type: SettingType::String,
                        encrypted: true,
                        permission: 'settings.manage_critical',
                        configKey: $secret['config'] ?? null,
                        envKey: $secret['env'] ?? null,
                        envFile: (bool) ($secret['env_file'] ?? false),
                        format: $secret['format'] ?? null,
                    );
                }
            }

            foreach ((array) ($group['vault_keys'] ?? []) as $key => $vaultKey) {
                $definitions[] = new SettingDefinition(
                    screen: (string) $screen,
                    group: $store,
                    key: (string) $key,
                    type: self::typeOf((string) $vaultKey['type']),
                    encrypted: (bool) $vaultKey['encrypted'],
                    permission: 'settings.manage_critical',
                    configKey: $vaultKey['config'] ?? null,
                    envKey: $vaultKey['env'] ?? null,
                    vault: true,
                );
            }

            if (($group['app_config'] ?? false) === true) {
                foreach ((array) ($group['env'] ?? []) as $key => $envKey) {
                    $definitions[] = new SettingDefinition(
                        screen: (string) $screen,
                        group: AppConfigSetting::GROUP,
                        key: (string) $key,
                        type: AppConfigSetting::from((string) $key)->type(),
                        encrypted: false,
                        permission: $permission,
                        envKey: (string) $envKey,
                    );
                }
            }
        }

        return $definitions;
    }

    /**
     * The definitions applied over config keys.
     *
     * @return list<SettingDefinition>
     */
    public function overlaid(): array
    {
        return array_values(array_filter($this->all(), static fn (SettingDefinition $definition): bool => $definition->configKey !== null));
    }

    /**
     * The code-vault key definitions (group digital_codes), by key.
     *
     * @return array<string, SettingDefinition>
     */
    public function vault(): array
    {
        $vault = [];

        foreach ($this->all() as $definition) {
            if ($definition->vault) {
                $vault[$definition->key] = $definition;
            }
        }

        return $vault;
    }

    /**
     * Config keys that follow another config key while no setting of their own is stored.
     *
     * @return array<string, array{from: string, as: string}>
     */
    public function derived(): array
    {
        return (array) config('admin_settings.derived', []);
    }

    public function find(string $group, string $key): ?SettingDefinition
    {
        foreach ($this->all() as $definition) {
            if ($definition->group === $group && $definition->key === $key) {
                return $definition;
            }
        }

        return null;
    }

    public static function typeOf(string $fieldType): SettingType
    {
        return match ($fieldType) {
            'int' => SettingType::Int,
            'bool' => SettingType::Bool,
            'decimal' => SettingType::Decimal,
            'json' => SettingType::Json,
            default => SettingType::String,
        };
    }
}
