<?php

namespace App\Domains\Orders\Repositories;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use Illuminate\Support\Collection;

class OrderRepository
{
    /**
     * An open order already placed with one of these placement hashes.
     *
     * @param  list<string>  $hashes
     */
    public function findByPlacement(array $hashes): ?Order
    {
        return Order::query()->whereIn('placement_hash', $hashes)->orderBy('id')->first();
    }

    /**
     * Unpaid orders whose quote has expired (idx_o_expire).
     *
     * @return Collection<int, int>
     */
    public function expiredUnpaidIds(int $limit): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at')
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * Lock an order for update, skipping it when another transaction holds it.
     */
    public function lockIfFree(int $orderId): ?Order
    {
        return Order::query()->whereKey($orderId)->lock('for update skip locked')->first();
    }

    public function findByPublicId(string $publicId): ?Order
    {
        return Order::query()->where('public_id', $publicId)->first();
    }
}
