<?php

namespace Database\Factories\Support;

use App\Domains\Support\Enums\SupportTicketCategory;
use App\Domains\Support\Enums\SupportTicketPriority;
use App\Domains\Support\Enums\SupportTicketStatus;
use App\Domains\Support\Models\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => fake()->unique()->lexify('????????????????'),
            'category' => SupportTicketCategory::Order,
            'subject' => fake()->words(2, true),
            'status' => SupportTicketStatus::Open,
            'priority' => SupportTicketPriority::Low,
            'last_activity_at' => now(),
        ];
    }
}
