<?php

namespace Database\Factories\Settings;

use App\Domains\Identity\Models\User;
use App\Domains\Settings\Models\SavedFilter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedFilter>
 */
class SavedFilterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'table_key' => fake()->unique()->lexify('????????????'),
            'name' => fake()->unique()->lexify('????????????????'),
            'payload' => ['key' => 'value'],
        ];
    }
}
