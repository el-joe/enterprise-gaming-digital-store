<?php

namespace Database\Factories\Identity;

use App\Domains\Identity\Enums\OtpChallengePurpose;
use App\Domains\Identity\Models\OtpChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OtpChallenge>
 */
class OtpChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purpose' => OtpChallengePurpose::GuestOrderAccess,
            'identifier_hash' => fake()->sha256(),
            'code_hash' => fake()->sha256(),
            'expires_at' => now(),
        ];
    }
}
