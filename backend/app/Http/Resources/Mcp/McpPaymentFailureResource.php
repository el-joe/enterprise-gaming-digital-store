<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Payments\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A failed payment attempt: gateway, amount and failure code. Provider ids, messages and card
 * details are not included.
 *
 * @mixin Payment
 */
class McpPaymentFailureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'order_number' => $this->order?->order_number,
            'order_status' => $this->order?->status->value,
            'gateway' => $this->paymentGateway?->key,
            'attempt_no' => $this->attempt_no,
            'status' => $this->status->value,
            'amount' => McpFormat::money($this->amount, $this->currency),
            'failure_code' => $this->failure_code,
            'created_at' => McpFormat::iso($this->created_at),
            'updated_at' => McpFormat::iso($this->updated_at),
        ];
    }
}
