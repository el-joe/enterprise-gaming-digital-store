<?php

namespace Database\Factories\Settings;

use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\ExportJobStatus;
use App\Domains\Settings\Models\ExportJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExportJob>
 */
class ExportJobFactory extends Factory
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
            'table_key' => fake()->lexify('????????????'),
            'status' => ExportJobStatus::Pending,
        ];
    }
}
