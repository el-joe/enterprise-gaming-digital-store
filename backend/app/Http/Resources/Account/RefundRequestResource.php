<?php

namespace App\Http\Resources\Account;

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
            'order' => ['public_id' => $this->order?->public_id, 'order_number' => $this->order?->order_number],
            'reason' => $this->reason,
            'status' => $this->status->value,
            'refund' => $this->refund === null ? null : ['public_id' => $this->refund->public_id, 'status' => $this->refund->status->value],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
