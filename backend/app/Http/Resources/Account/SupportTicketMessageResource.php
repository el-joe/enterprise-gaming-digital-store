<?php

namespace App\Http\Resources\Account;

use App\Domains\Support\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A public message for the customer. An internal note resolves to nothing at all.
 *
 * @mixin SupportTicketMessage
 */
class SupportTicketMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->is_internal) {
            return [];
        }

        return [
            'id' => $this->id,
            'from' => $this->author_type->value,
            'body' => $this->body,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
