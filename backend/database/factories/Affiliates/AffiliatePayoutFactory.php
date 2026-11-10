<?php

namespace Database\Factories\Affiliates;

use App\Domains\Affiliates\Enums\AffiliatePayoutStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliatePayout;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliatePayout>
 */
class AffiliatePayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'amount_base' => '10.000000',
            'status' => AffiliatePayoutStatus::Pending,
            'created_by' => User::factory(),
        ];
    }
}
