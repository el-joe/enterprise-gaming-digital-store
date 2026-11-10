<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Shared\Enums\EnvironmentMode;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->lexify('????????????????'),
            'name' => fake()->words(2, true),
            'adapter_key' => fake()->lexify('adapter_????????'),
            'mode' => EnvironmentMode::Sandbox,
            'default_currency' => fn (): string => CurrencyFactory::ensure('USD'),
        ];
    }
}
