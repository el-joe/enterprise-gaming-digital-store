<?php

namespace Database\Factories\Support;

use App\Domains\Support\Enums\SupportTicketMessageAuthorType;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketMessage>
 */
class SupportTicketMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_ticket_id' => SupportTicket::factory(),
            'author_type' => SupportTicketMessageAuthorType::Customer,
            'body' => fake()->sentence(),
        ];
    }
}
