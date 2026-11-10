<?php

namespace Database\Factories\Customers;

use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Customers\Models\CustomerConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerConsent>
 */
class CustomerConsentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel' => CustomerConsentChannel::EmailMarketing,
            'granted' => false,
            'source' => fake()->lexify('????????????'),
        ];
    }
}
