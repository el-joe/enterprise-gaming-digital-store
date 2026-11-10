<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

/**
 * Cancels unpaid orders whose quote expired (`orders:expire-unpaid`). Each order is handled in
 * its own transaction; one held by a payment webhook is skipped and picked up next run, and
 * the status is re-read under the lock, so a payment that lands first always wins.
 */
class OrderExpiryService
{
    public function __construct(private OrderRepository $orders, private OrderStateMachine $machine) {}

    /**
     * @return array{cancelled: int, skipped: int}
     */
    public function expireDue(int $limit = 500): array
    {
        $result = ['cancelled' => 0, 'skipped' => 0];

        foreach ($this->orders->expiredUnpaidIds($limit) as $orderId) {
            $cancelled = DB::transaction(function () use ($orderId): bool {
                $order = $this->orders->lockIfFree($orderId);

                if ($order === null || $order->status !== OrderStatus::PendingPayment || $order->expires_at === null || $order->expires_at->isFuture()) {
                    return false;
                }

                $this->machine->transition($order, OrderStatus::Cancelled, OrderActor::system(), 'Payment window expired.', ['reason' => 'quote_expired']);

                return true;
            });

            $result[$cancelled ? 'cancelled' : 'skipped']++;
        }

        return $result;
    }
}
