<?php

namespace Database\Factories\Identity;

use App\Domains\Identity\Models\MobileBiometricChallenge;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MobileBiometricChallenge>
 */
class MobileBiometricChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mobile_biometric_key_id' => MobileBiometricKey::factory(),
            'access_token_id' => static fn (): int => (int) User::factory()->create()->createToken('Pixel 9', ['customer'])->accessToken->getKey(),
            'challenge_hash' => fake()->unique()->sha256(),
            'expires_at' => now()->addMinute(),
        ];
    }
}
