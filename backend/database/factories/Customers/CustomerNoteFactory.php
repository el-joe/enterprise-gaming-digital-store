<?php

namespace Database\Factories\Customers;

use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerNote>
 */
class CustomerNoteFactory extends Factory
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
            'author_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
