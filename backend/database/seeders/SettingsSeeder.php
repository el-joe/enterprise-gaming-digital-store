<?php

namespace Database\Seeders;

use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed documented defaults. Existing values are kept so a re-seed never undoes an admin change.
     */
    public function run(): void
    {
        $settings = [
            ['currency', 'base', 'USD', SettingType::String],
            ['currency', 'max_age_minutes', '1440', SettingType::Int],
            ['currency', 'max_change_pct', '10', SettingType::Decimal],
            ['suppliers', 'selection_strategy', 'priority', SettingType::String],
            ['orders', 'quote_ttl_minutes', '30', SettingType::Int],
            ['orders', 'max_payment_attempts', '5', SettingType::Int],
            ['automation', 'task_drivers', '{}', SettingType::Json],
        ];

        foreach ($settings as [$group, $key, $value, $type]) {
            Setting::query()->firstOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value, 'type' => $type, 'is_encrypted' => false],
            );
        }
    }
}
