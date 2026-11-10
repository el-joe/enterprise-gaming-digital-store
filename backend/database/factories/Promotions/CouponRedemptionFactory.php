<?php

namespace Database\Factories\Promotions;

use App\Domains\Orders\Models\Order;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CouponRedemption>
 */
class CouponRedemptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'order_id' => Order::factory(),
            'discount_base' => '10.000000',
            'status' => CouponRedemptionStatus::Reserved,
        ];
    }
}
