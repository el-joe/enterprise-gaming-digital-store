<?php

namespace Database\Factories\Identity;

use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MobileRefreshToken>
 */
class MobileRefreshTokenFactory extends Factory
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
            'family_id' => (string) Str::ulid(),
            'token_hash' => fake()->unique()->sha256(),
            'device_name' => 'Pixel 9',
            'expires_at' => now()->addDays(30),
        ];
    }
}
