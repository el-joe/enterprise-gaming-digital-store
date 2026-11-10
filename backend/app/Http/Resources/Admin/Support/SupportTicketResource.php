<?php

namespace App\Http\Resources\Admin\Support;

use App\Domains\Support\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The staff view of a ticket, internal notes included and marked.
 *
 * @mixin SupportTicket
 */
class SupportTicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'ticket_number' => $this->ticket_number,
            'category' => $this->category->value,
            'subject' => $this->subject,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'customer' => $this->whenLoaded('user', fn (): ?array => $this->user === null ? null : ['id' => $this->user->id, 'name' => $this->user->name, 'email' => $this->user->email]),
            'guest_email' => $this->guest_email,
            'order' => $this->whenLoaded('order', fn (): ?array => $this->order === null ? null : ['public_id' => $this->order->public_id, 'order_number' => $this->order->order_number]),
            'assigned_to' => $this->whenLoaded('assignedTo', fn (): ?array => $this->assignedTo === null ? null : ['id' => $this->assignedTo->id, 'name' => $this->assignedTo->name]),
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'messages' => $this->whenLoaded('supportTicketMessages', fn (): array => SupportTicketMessageResource::collection($this->supportTicketMessages)->resolve()),
        ];
    }
}
