<?php

namespace App\Http\Resources\Admin\Support;

use App\Domains\Support\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RefundRequest
 */
class RefundRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => $this->whenLoaded('order', fn (): array => ['public_id' => $this->order->public_id, 'order_number' => $this->order->order_number, 'status' => $this->order->status->value]),
            'customer' => $this->whenLoaded('user', fn (): ?array => $this->user === null ? null : ['id' => $this->user->id, 'name' => $this->user->name, 'email' => $this->user->email]),
            'ticket' => $this->whenLoaded('supportTicket', fn (): ?array => $this->supportTicket === null ? null : ['public_id' => $this->supportTicket->public_id, 'ticket_number' => $this->supportTicket->ticket_number]),
            'reason' => $this->reason,
            'status' => $this->status->value,
            'reviewed_by' => $this->reviewed_by,
            'refund' => $this->whenLoaded('refund', fn (): ?array => $this->refund === null ? null : ['public_id' => $this->refund->public_id, 'status' => $this->refund->status->value]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
