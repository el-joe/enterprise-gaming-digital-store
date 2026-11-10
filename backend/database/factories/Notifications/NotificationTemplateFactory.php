<?php

namespace Database\Factories\Notifications;

use App\Domains\Notifications\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->lexify('????????????????'),
            'locale' => 'en',
            'body' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
