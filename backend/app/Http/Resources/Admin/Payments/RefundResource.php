<?php

namespace App\Http\Resources\Admin\Payments;

use App\Domains\Payments\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Refund
 */
class RefundResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'status' => $this->status->value,
            'reason' => $this->reason->value,
            'destination' => $this->destination->value,
            'amount' => ['amount' => $this->amount, 'currency' => $this->currency],
            'amount_base' => $this->amount_base,
            'failure_reason' => $this->failure_reason,
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
