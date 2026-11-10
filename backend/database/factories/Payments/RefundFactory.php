<?php

namespace Database\Factories\Payments;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundRequestedByType;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Refund;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
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
            'destination' => RefundDestination::OriginalMethod,
            'status' => RefundStatus::Requested,
            'reason' => RefundReason::Undelivered,
            'amount' => '10.000000',
            'currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'amount_base' => '10.000000',
            'requested_by_type' => RefundRequestedByType::System,
        ];
    }
}
