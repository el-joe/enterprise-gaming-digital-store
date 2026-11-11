<?php

namespace App\Domains\Settings\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\AppConfigSetting;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
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
 */
class AdminSettingsService
{
    public function __construct(private SettingsService $settings) {}

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
     * @return array{key: string, permission: string, applied: bool, fields: array<string, array<string, mixed>>, secrets: array<string, bool>, readonly: array<string, string>}
     */
    public function screen(string $group): array
    {
        $definition = $this->definition($group);
        $store = $definition['store'] ?? null;
        $fields = [];

        foreach ($this->fieldsOf($group) as $key => $field) {
            $fields[$key] = [...$field, 'value' => $store === null ? null : $this->settings->get($store, $key, $field['default'] ?? null)];
        }

        $secrets = [];

        foreach (array_keys((array) ($definition['secrets'] ?? [])) as $key) {
            $secrets[$key] = Setting::query()->where('group', $store)->where('key', $key)->whereNotNull('value')->exists();
        }

        return [
            'key' => $group,
            'permission' => (string) $definition['permission'],
            'applied' => (bool) ($definition['applied'] ?? false),
            'fields' => $fields,
            'secrets' => $secrets,
            'readonly' => $this->readonly((array) ($definition['readonly'] ?? [])),
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

        if ($store === null) {
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

            if ($value !== $this->settings->get($store, $key, $field['default'] ?? null)) {
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
    public function setSecret(User $actor, string $group, string $key, ?string $value): void
    {
        $definition = $this->definition($group);
        $this->authorize($actor, 'settings.manage_critical');
        $secret = $definition['secrets'][$key] ?? throw new NotFoundHttpException;

        if ($value !== null) {
            Validator::make(['value' => $value], ['value' => $secret['rules']])->validate();
        }

        $this->settings->set((string) $definition['store'], $key, $value, SettingType::String, encrypted: true, actor: $actor);
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
            default => [$value === null || $value === '' ? null : (string) $value, SettingType::String],
        };
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
