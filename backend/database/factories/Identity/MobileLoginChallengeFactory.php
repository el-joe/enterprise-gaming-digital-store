<?php

namespace Database\Factories\Identity;

use App\Domains\Identity\Models\MobileLoginChallenge;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MobileLoginChallenge>
 */
class MobileLoginChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withTwoFactor(),
            'token_hash' => fake()->unique()->sha256(),
            'device_name' => 'Pixel 9',
            'expires_at' => now()->addMinutes(5),
        ];
    }
}
