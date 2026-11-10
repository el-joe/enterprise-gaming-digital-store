<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Mcp\Services\PiiMasker;
use App\Domains\Orders\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Order summary with masked customer identifiers. No IP address, user agent, delivery values,
 * payment references or digital codes.
 *
 * @mixin Order
 */
class McpOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $masker = app(PiiMasker::class);

        return [
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_changed_at' => McpFormat::iso($this->status_changed_at),
            'customer' => [
                'type' => $this->user_id === null ? 'guest' : 'registered',
                'email' => $masker->email($this->contact_email),
            ],
            'country' => $this->country_code,
            'total_base' => McpFormat::money($this->total_base, $this->base_currency),
            'display_total' => McpFormat::money($this->display_total, $this->display_currency),
            'payment_total' => McpFormat::money($this->payment_total, $this->payment_currency),
            'payment_gateway' => $this->paymentGateway?->key,
            'coupon_code' => $this->coupon_code,
            'units_count' => $this->whenCounted('orderItemUnits'),
            'created_at' => McpFormat::iso($this->created_at),
            'paid_at' => McpFormat::iso($this->paid_at),
            'completed_at' => McpFormat::iso($this->completed_at),
        ];
    }
}
