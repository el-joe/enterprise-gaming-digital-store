<?php

namespace App\Http\Resources\Account;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Wallet\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A statement line for the customer: no internal references, staff ids or keys.
 *
 * `order` (M-A15) is the customer's own order behind a purchase debit (`payment` reference) or
 * a refund credit (`refund` reference), as its public id and number; null for staff adjustments
 * and for anything that is not the requesting customer's order.
 *
 * @mixin WalletTransaction
 */
class WalletTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->linkedOrder($request);

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'amount' => $this->amount,
            'balance_after' => $this->balance_after,
            'note' => $this->note,
            'order' => $order === null ? null : ['public_id' => $order->public_id, 'order_number' => $order->order_number],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function linkedOrder(Request $request): ?Order
    {
        if (! $this->relationLoaded('reference')) {
            return null;
        }

        $reference = $this->reference;
        $order = $reference instanceof Payment || $reference instanceof Refund ? $reference->order : null;
        $userId = $request->user()?->getAuthIdentifier();

        return $order !== null && $userId !== null && $order->user_id === $userId ? $order : null;
    }
}
