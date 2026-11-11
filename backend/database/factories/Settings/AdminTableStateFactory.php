<?php

namespace Database\Factories\Settings;

use App\Domains\Identity\Models\User;
use App\Domains\Settings\Models\AdminTableState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminTableState>
 */
class AdminTableStateFactory extends Factory
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
            'table_key' => 'orders',
            'hidden_columns' => [],
            'page_length' => 25,
        ];
    }
}
