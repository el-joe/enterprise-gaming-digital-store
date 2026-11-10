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
 * decrypted only when read.
 */
class SettingsService
{
    private const int CACHE_SECONDS = 3600;

    public function __construct(private AuditLogger $audit) {}

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $row = $this->loadGroup($group)[$key] ?? null;

        return $row === null ? $default : $this->decode($row);
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
                after: $encrypted ? null : ['value' => $value],
            ));
        });

        Cache::forget($this->cacheKey($group));
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
    private function decode(array $row): mixed
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
