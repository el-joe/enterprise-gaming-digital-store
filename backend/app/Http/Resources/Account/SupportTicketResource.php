<?php

namespace App\Http\Resources\Account;

use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A ticket as its customer sees it. Internal notes are filtered out here even when the loaded
 * relation contains them, so this resource can never leak one; no staff identity or assignment
 * is shown.
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
            'order' => $this->order === null ? null : ['public_id' => $this->order->public_id, 'order_number' => $this->order->order_number],
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'messages' => $this->whenLoaded('supportTicketMessages', fn (): array => SupportTicketMessageResource::collection(
                $this->supportTicketMessages->reject(static fn (SupportTicketMessage $message): bool => $message->is_internal)->values(),
            )->resolve()),
        ];
    }
}
