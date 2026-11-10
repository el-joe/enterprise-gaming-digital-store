<?php

namespace App\Domains\Orders\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Order status for the payment return page. Only the owning customer, or the browser session
 * that placed or verified a guest order, may read it; anyone else gets not found. It reports
 * what the server knows and never confirms payment from the browser.
 */
class OrderStatusReader
{
    public const string SESSION_KEY = 'checkout.orders';

    /**
     * @param  list<string>  $sessionOrderIds
     * @return array{public_id: string, status: string, payment_status: string, is_final: bool}
     */
    public function forViewer(string $publicId, ?User $user, array $sessionOrderIds): array
    {
        $order = Order::query()->where('public_id', $publicId)->first();

        $allowed = $order !== null && (
            ($user !== null && $order->user_id === $user->id)
            || ($order->user_id === null && in_array($order->public_id, $sessionOrderIds, true))
        );

        if (! $allowed) {
            throw (new ModelNotFoundException)->setModel(Order::class);
        }

        return [
            'public_id' => $order->public_id,
            'status' => $order->status->value,
            'payment_status' => $this->paymentStatus($order),
            'is_final' => $order->status->isClosed(),
        ];
    }

    private function paymentStatus(Order $order): string
    {
        return match (true) {
            $order->status === OrderStatus::PaymentProcessing => 'processing',
            $order->status === OrderStatus::Refunded => 'refunded',
            $order->status === OrderStatus::PartiallyRefunded => 'partially_refunded',
            $order->paid_at !== null => 'paid',
            default => 'unpaid',
        };
    }
}
