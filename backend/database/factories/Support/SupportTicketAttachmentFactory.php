<?php

namespace Database\Factories\Support;

use App\Domains\Cms\Models\Media;
use App\Domains\Support\Models\SupportTicketAttachment;
use App\Domains\Support\Models\SupportTicketMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketAttachment>
 */
class SupportTicketAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_ticket_message_id' => SupportTicketMessage::factory(),
            'media_id' => Media::factory(),
        ];
    }
}
