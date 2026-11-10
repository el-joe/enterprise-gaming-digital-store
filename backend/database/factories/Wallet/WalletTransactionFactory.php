<?php

namespace Database\Factories\Wallet;

use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_wallet_id' => CustomerWallet::factory(),
            'type' => WalletTransactionType::CreditRefund,
            'amount' => '10.000000',
            'balance_after' => '10.000000',
            'idempotency_key' => fake()->unique()->lexify('????????????????'),
        ];
    }
}
