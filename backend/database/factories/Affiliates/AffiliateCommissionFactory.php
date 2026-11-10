<?php

namespace Database\Factories\Affiliates;

use App\Domains\Affiliates\Enums\AffiliateCommissionStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Orders\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateCommission>
 */
class AffiliateCommissionFactory extends Factory
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
            'order_id' => Order::factory(),
            'amount_base' => '10.000000',
            'status' => AffiliateCommissionStatus::Pending,
        ];
    }
}
