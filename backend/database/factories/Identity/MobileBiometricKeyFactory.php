<?php

namespace Database\Factories\Identity;

use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MobileBiometricKey>
 */
class MobileBiometricKeyFactory extends Factory
{
    /**
     * A fixed P-256 public key; tests that sign generate their own key pair.
     */
    private const string PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEh2whIYalZlmg+0M+PDGebuU8wo7p\nE6O2Tyq9vDKb02xoix4TRanCSGJVHvxzkOsuT3RAmb3601U6sc1R6xCbJw==\n-----END PUBLIC KEY-----\n";

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'user_id' => User::factory(),
            'family_id' => (string) Str::ulid(),
            'algorithm' => 'ES256',
            'public_key' => self::PUBLIC_KEY,
            'key_fingerprint' => fake()->unique()->sha256(),
        ];
    }
}
