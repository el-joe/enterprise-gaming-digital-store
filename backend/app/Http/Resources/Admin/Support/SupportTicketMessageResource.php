<?php

namespace App\Http\Resources\Admin\Support;

use App\Domains\Support\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportTicketMessage
 */
class SupportTicketMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_type' => $this->author_type->value,
            'author_id' => $this->author_id,
            'body' => $this->body,
            'is_internal' => $this->is_internal,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
