<?php

namespace Database\Factories\Fulfillment;

use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalCodeAccess>
 */
class DigitalCodeAccessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'digital_code_id' => DigitalCode::factory(),
            'actor_type' => DigitalCodeAccessActorType::Customer,
            'purpose' => DigitalCodeAccessPurpose::CustomerReveal,
            'granted' => false,
        ];
    }
}
