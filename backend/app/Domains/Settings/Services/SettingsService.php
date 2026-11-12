<?php

namespace App\Domains\Settings\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Typed, cached application settings. Encrypted values stay encrypted in the cache and are
 * decrypted only when read. Every save is audited (an encrypted value only as changed or
 * cleared, never the value) and moves the SettingsOverlay version, so config keys backed by a
 * setting follow the change in every process.
 */
class SettingsService
{
    private const int CACHE_SECONDS = 3600;

    public function __construct(private AuditLogger $audit, private SettingsOverlay $overlay) {}

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $row = $this->loadGroup($group)[$key] ?? null;

        return $row === null ? $default : self::decodeStored($row);
    }

    public function set(
        string $group,
        string $key,
        mixed $value,
        SettingType $type,
        bool $encrypted = false,
        ?User $actor = null,
    ): void {
        $encoded = $this->encode($value, $type);

        DB::transaction(function () use ($group, $key, $value, $type, $encrypted, $actor, $encoded): void {
            $setting = Setting::query()->where('group', $group)->where('key', $key)->lockForUpdate()->first()
                ?? new Setting(['group' => $group, 'key' => $key]);

            $before = $setting->exists && ! $setting->is_encrypted ? ['value' => $setting->value] : null;

            $setting->fill([
                'value' => $encrypted && $encoded !== null ? Crypt::encryptString($encoded) : $encoded,
                'type' => $type,
                'is_encrypted' => $encrypted,
                'updated_by' => $actor?->id,
            ])->save();

            $this->audit->record(new AuditEntry(
                action: 'settings.updated',
                subject: $setting,
                before: $before,
                after: $encrypted ? ['value' => $value === null ? 'cleared' : 'changed'] : ['value' => $value],
            ));
        });

        Cache::forget($this->cacheKey($group));
        $this->overlay->changed();
    }

    /**
     * Whether a value is stored (secrets included, without decrypting them).
     */
    public function has(string $group, string $key): bool
    {
        return ($this->loadGroup($group)[$key]['value'] ?? null) !== null;
    }

    /**
     * The stored row of a setting, as stored (an encrypted value stays encrypted), or null.
     *
     * @return array{value: string|null, type: string, is_encrypted: bool, updated_by: int|null}|null
     */
    public function stored(string $group, string $key): ?array
    {
        $setting = Setting::query()->where('group', $group)->where('key', $key)->first();

        return $setting === null ? null : ['value' => $setting->value, 'type' => $setting->type->value, 'is_encrypted' => $setting->is_encrypted, 'updated_by' => $setting->updated_by];
    }

    /**
     * @return array<string, array{value: string|null, type: string, is_encrypted: bool}>
     */
    private function loadGroup(string $group): array
    {
        return Cache::remember($this->cacheKey($group), self::CACHE_SECONDS, static fn (): array => Setting::query()
            ->where('group', $group)
            ->get()
            ->mapWithKeys(static fn (Setting $setting): array => [$setting->key => [
                'value' => $setting->value,
                'type' => $setting->type->value,
                'is_encrypted' => $setting->is_encrypted,
            ]])
            ->all());
    }

    private function cacheKey(string $group): string
    {
        return "settings:{$group}";
    }

    private function encode(mixed $value, SettingType $type): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value)) {
            throw new InvalidArgumentException('Settings do not accept floats; pass a decimal string.');
        }

        return match ($type) {
            SettingType::Json => json_encode($value, JSON_THROW_ON_ERROR),
            SettingType::Bool => $value ? '1' : '0',
            SettingType::Int => (string) (int) $value,
            SettingType::String, SettingType::Decimal => (string) $value,
        };
    }

    /**
     * @param  array{value: string|null, type: string, is_encrypted: bool}  $row
     */
    public static function decodeStored(array $row): mixed
    {
        if ($row['value'] === null) {
            return null;
        }

        $value = $row['is_encrypted'] ? Crypt::decryptString($row['value']) : $row['value'];

        return match (SettingType::from($row['type'])) {
            SettingType::Json => json_decode($value, true, flags: JSON_THROW_ON_ERROR),
            SettingType::Bool => $value === '1',
            SettingType::Int => (int) $value,
            SettingType::String, SettingType::Decimal => $value,
        };
    }
}
