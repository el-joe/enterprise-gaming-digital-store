<?php

namespace App\Domains\Settings\Services;

use App\Domains\Fulfillment\Services\DigitalCodeKeyManager;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\AppConfigSetting;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Rules\SettingValueFormat;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The admin settings screens (M15, spec Phase 9.12, `config/admin_settings.php`). Values are
 * typed and validated per field, saved through SettingsService (cached, audited per key) and
 * only by holders of the group's permission. Secrets are write-only: this service never returns
 * a secret value, only whether one is stored. The mobile group edits the public app config
 * (`AppConfigSetting`, M-A4) with that enum's own parsing.
 *
 * Fields and secrets with `config` replaced `.env` keys: their default is the config file's
 * built-in value (SettingsOverlay::defaultFor), their `format` adds a SettingValueFormat rule,
 * and a save reaches running code through the overlay.
 *
 * The digital_codes group (owner decision 11) has no editable value: its screen shows only the
 * code-vault status (configured, active key id, number of keys) to holders of every one of its
 * `view_permissions`, and saving it is refused; the keys change only through artisan commands.
 */
class AdminSettingsService
{
    public function __construct(private SettingsService $settings, private SettingsOverlay $overlay, private DigitalCodeKeyManager $vaultKeys) {}

    /**
     * @return list<string>
     */
    public function groups(): array
    {
        return array_keys((array) config('admin_settings.groups'));
    }

    /**
     * Everything a group's screen shows: fields with current values, secret states and
     * read-only values. Never a secret value.
     *
     * @return array{key: string, permission: string, applied: bool, fields: array<string, array<string, mixed>>, secrets: array<string, bool>, uploads: list<string>, readonly: array<string, string>, vault: array{configured: bool, active_kid: int|null, key_count: int, blind_index: bool, stored: bool}|null, view_permissions: list<string>}
     */
    public function screen(string $group): array
    {
        $definition = $this->definition($group);
        $store = $definition['store'] ?? null;
        $fields = [];

        foreach ($this->fieldsOf($group) as $key => $field) {
            $fields[$key] = [...$field, 'value' => $store === null ? null : $this->settings->get($store, $key, $this->defaultOf($field))];
        }

        $secrets = [];
        $uploads = [];

        foreach ((array) ($definition['secrets'] ?? []) as $key => $secret) {
            $secrets[$key] = $this->settings->has((string) $store, $key);

            if (($secret['upload'] ?? false) === true) {
                $uploads[] = (string) $key;
            }
        }

        return [
            'key' => $group,
            'permission' => (string) $definition['permission'],
            'applied' => (bool) ($definition['applied'] ?? false),
            'fields' => $fields,
            'secrets' => $secrets,
            'uploads' => $uploads,
            'readonly' => $this->readonly((array) ($definition['readonly'] ?? [])),
            'vault' => ($definition['vault_keys'] ?? []) === [] ? null : $this->vaultKeys->status(),
            'view_permissions' => array_values((array) ($definition['view_permissions'] ?? [])),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     * @throws ApplicationException
     */
    public function save(User $actor, string $group, array $input): void
    {
        $definition = $this->definition($group);
        $this->authorize($actor, (string) $definition['permission']);
        $store = $definition['store'] ?? null;

        if ($store === null || ($definition['vault_keys'] ?? []) !== []) {
            throw new ApplicationException(__('admin.settings.read_only_group'), 'conflict', 409);
        }

        if (($definition['app_config'] ?? false) === true) {
            $this->saveAppConfig($actor, $input);

            return;
        }

        $fields = $this->fieldsOf($group);
        $rules = [];

        foreach ($fields as $key => $field) {
            $rules[$key] = $field['rules'];

            if (isset($field['format'])) {
                $rules[$key][] = new SettingValueFormat((string) $field['format'], (array) ($field['placeholders'] ?? []));
            }

            if (isset($field['item_rules'])) {
                $rules["{$key}.*"] = $field['item_rules'];
            }
        }

        $values = Validator::make($input, $rules, attributes: array_combine(array_keys($fields), array_map(static fn (string $key): string => (string) __("admin.settings.field.{$key}"), array_keys($fields))))->validate();

        foreach ($fields as $key => $field) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            [$value, $type] = $this->typed($field, $values[$key]);

            if ($value !== $this->settings->get($store, $key, $this->defaultOf($field))) {
                $this->settings->set($store, $key, $value, $type, actor: $actor);
            }
        }
    }

    /**
     * Replace (or, with null, clear) a write-only secret. Always a critical setting.
     *
     * @throws ValidationException
     * @throws ApplicationException
     */
    public function setSecret(User $actor, string $group, string $key, ?string $value, bool $upload = false): void
    {
        $definition = $this->definition($group);
        $this->authorize($actor, 'settings.manage_critical');
        $secret = $definition['secrets'][$key] ?? throw new NotFoundHttpException;

        if ($upload && ($secret['upload'] ?? false) !== true) {
            throw ValidationException::withMessages(['file' => [__('admin.settings.upload_not_allowed')]]);
        }

        if ($value !== null) {
            $rules = $secret['rules'];

            if (isset($secret['format'])) {
                $rules[] = new SettingValueFormat((string) $secret['format']);
            }

            Validator::make(['value' => $value], ['value' => $rules], attributes: ['value' => (string) __("admin.settings.field.{$key}")])->validate();
        }

        $this->settings->set((string) $definition['store'], $key, $value, SettingType::String, encrypted: true, actor: $actor);
    }

    /**
     * The validation rules of one field or secret of a group (its `format` included), for values
     * that do not come through the screen (settings:import-env).
     *
     * @return array<int, mixed>
     */
    public function rulesFor(string $group, string $key): array
    {
        $definition = $this->definition($group);
        $item = $definition['fields'][$key] ?? $definition['secrets'][$key] ?? throw new NotFoundHttpException;
        $rules = (array) $item['rules'];

        if (isset($item['format'])) {
            $rules[] = new SettingValueFormat((string) $item['format'], (array) ($item['placeholders'] ?? []));
        }

        return $rules;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fieldsOf(string $group): array
    {
        $definition = $this->definition($group);

        if (($definition['app_config'] ?? false) !== true) {
            return (array) ($definition['fields'] ?? []);
        }

        $fields = [];

        foreach (AppConfigSetting::cases() as $setting) {
            $fields[$setting->value] = [
                'type' => match ($setting->type()) {
                    SettingType::Bool => 'bool',
                    SettingType::Json => 'json',
                    default => 'string',
                },
                'rules' => [],
                'default' => $setting->default(),
            ];
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function saveAppConfig(User $actor, array $input): void
    {
        $errors = [];
        $changes = [];

        foreach (AppConfigSetting::cases() as $setting) {
            if (! array_key_exists($setting->value, $input)) {
                continue;
            }

            $raw = $input[$setting->value];

            if ($setting->type() === SettingType::Json && ($raw === null || $raw === '' || $raw === [])) {
                $changes[$setting->value] = [$setting, null];

                continue;
            }

            if ($setting->type() === SettingType::Bool) {
                $raw = is_bool($raw) ? ($raw ? 'true' : 'false') : (string) $raw;
            } elseif ($setting->type() === SettingType::Json) {
                $raw = is_array($raw) ? (string) json_encode($raw, JSON_UNESCAPED_UNICODE) : (string) $raw;
            } else {
                $raw = trim((string) $raw);
            }

            if ($raw === '' && $setting->isUrl()) {
                $changes[$setting->value] = [$setting, null];

                continue;
            }

            $parsed = $setting->parse($raw);

            if ($parsed['error'] !== null) {
                $errors[$setting->value] = [$parsed['error']];

                continue;
            }

            $changes[$setting->value] = [$setting, $parsed['value']];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($changes as [$setting, $value]) {
            if ($value !== $this->settings->get(AppConfigSetting::GROUP, $setting->value, $setting->default())) {
                $this->settings->set(AppConfigSetting::GROUP, $setting->value, $value, $setting->type(), actor: $actor);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array{0: mixed, 1: SettingType}
     */
    private function typed(array $field, mixed $value): array
    {
        return match ($field['type']) {
            'int' => [$value === null || $value === '' ? null : (int) $value, SettingType::Int],
            'bool' => [(bool) $value, SettingType::Bool],
            'decimal' => [$value === null ? null : (string) $value, SettingType::Decimal],
            'json' => [$value ?? [], SettingType::Json],
            default => [$value === null || trim((string) $value) === '' ? null : trim((string) $value), SettingType::String],
        };
    }

    /**
     * The value used while nothing is stored: the config file's built-in default for a field that
     * replaced a `.env` key, else the field's `default`.
     *
     * @param  array<string, mixed>  $field
     */
    private function defaultOf(array $field): mixed
    {
        return isset($field['config']) ? $this->overlay->defaultFor((string) $field['config']) : ($field['default'] ?? null);
    }

    /**
     * @param  array<string, string>  $sources
     * @return array<string, string>
     */
    private function readonly(array $sources): array
    {
        $values = [];

        foreach ($sources as $key => $source) {
            if (str_starts_with($source, 'settings:')) {
                [$path, $default] = array_pad(explode(':', substr($source, 9), 2), 2, '');
                [$store, $settingKey] = explode('.', $path, 2);
                $values[$key] = (string) $this->settings->get($store, $settingKey, $default);

                continue;
            }

            $value = config($source);
            $values[$key] = is_bool($value) ? ($value ? 'on' : 'off') : (string) $value;
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(string $group): array
    {
        $definition = config("admin_settings.groups.{$group}");

        return is_array($definition) ? $definition : throw new NotFoundHttpException;
    }

    private function authorize(User $actor, string $permission): void
    {
        if (! $actor->can($permission)) {
            throw new ApplicationException(__('admin.settings.forbidden', ['permission' => $permission]), 'forbidden', 403);
        }
    }
}
