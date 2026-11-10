<?php

namespace Database\Factories\Settings;

use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => fake()->unique()->lexify('????????????'),
            'key' => fake()->unique()->lexify('????????????????'),
            'type' => SettingType::String,
            'is_encrypted' => false,
        ];
    }
}
