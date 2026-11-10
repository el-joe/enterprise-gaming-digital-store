<?php

namespace Database\Factories\Wallet;

use App\Domains\Identity\Models\User;
use App\Domains\Wallet\Models\CustomerWallet;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerWallet>
 */
class CustomerWalletFactory extends Factory
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
            'currency' => fn (): string => CurrencyFactory::ensure('USD'),
        ];
    }
}
