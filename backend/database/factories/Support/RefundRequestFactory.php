<?php

namespace Database\Factories\Support;

use App\Domains\Orders\Models\Order;
use App\Domains\Support\Enums\RefundRequestStatus;
use App\Domains\Support\Models\RefundRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RefundRequest>
 */
class RefundRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'reason' => fake()->sentence(),
            'status' => RefundRequestStatus::Requested,
        ];
    }
}
