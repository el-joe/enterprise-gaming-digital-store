<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Enums\NewsletterSubscriberStatus;
use App\Domains\Cms\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'status' => NewsletterSubscriberStatus::Pending,
        ];
    }
}
