<?php

namespace Database\Factories\Fulfillment;

use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Models\DigitalCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalCode>
 */
class DigitalCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => DigitalCodeSource::Supplier,
            'code_ciphertext' => fake()->sentence(),
            'key_version' => 1,
            'code_hash' => fake()->unique()->sha256(),
            'masked_code' => '****'.fake()->numerify('####'),
        ];
    }
}
