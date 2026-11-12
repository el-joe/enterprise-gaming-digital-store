<?php

namespace Database\Seeders;

use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Services\SettingDefinitions;
use App\Domains\Settings\Services\SettingsOverlay;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed documented defaults. Existing values are kept so a re-seed never undoes an admin change.
     * The settings that replaced `.env` keys are seeded with their config file's built-in default
     * (feature switches off, no secrets); `settings:import-env` treats such untouched rows as unset.
     */
    public function run(SettingDefinitions $definitions, SettingsOverlay $overlay): void
    {
        $settings = [
            ['currency', 'base', 'USD', SettingType::String],
            ['currency', 'max_age_minutes', '1440', SettingType::Int],
            ['currency', 'max_change_pct', '10', SettingType::Decimal],
            ['pricing', 'default_below_margin_policy', 'block', SettingType::String],
            ['pricing', 'default_min_margin_pct', '0', SettingType::Decimal],
            ['pricing', 'default_min_margin_fixed', '0', SettingType::Decimal],
            ['pricing', 'default_gateway_fee_pct', '0', SettingType::Decimal],
            ['pricing', 'default_gateway_fee_fixed', '0', SettingType::Decimal],
            ['pricing', 'conversion_cost_pct', '0', SettingType::Decimal],
            ['suppliers', 'selection_strategy', 'priority', SettingType::String],
            ['orders', 'quote_ttl_minutes', '30', SettingType::Int],
            ['orders', 'max_payment_attempts', '5', SettingType::Int],
            ['security', 'risk_review_score', '40', SettingType::Int],
            ['security', 'risk_block_score', '80', SettingType::Int],
            ['security', 'risk_first_order_amount_base', '100', SettingType::Decimal],
            ['automation', 'task_drivers', '{}', SettingType::Json],
        ];

        foreach ($definitions->overlaid() as $definition) {
            $default = $overlay->defaultFor((string) $definition->configKey);

            if ($definition->encrypted || $default === null || $default === '') {
                continue;
            }

            $settings[] = [$definition->group, $definition->key, match ($definition->type) {
                SettingType::Bool => $default ? '1' : '0',
                SettingType::Int => (string) (int) $default,
                default => (string) $default,
            }, $definition->type];
        }

        foreach ($settings as [$group, $key, $value, $type]) {
            Setting::query()->firstOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value, 'type' => $type, 'is_encrypted' => false],
            );
        }
    }
}
