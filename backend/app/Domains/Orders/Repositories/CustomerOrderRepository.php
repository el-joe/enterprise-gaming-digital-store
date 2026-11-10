<?php

namespace App\Domains\Orders\Repositories;

use App\Domains\Orders\DTOs\CustomerOrderFilter;
use App\Domains\Orders\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Reads behind the customer's order history and order detail (M-A5, gap G4). Nothing here loads
 * a code: units carry only the masked code column of their digital code.
 */
class CustomerOrderRepository
{
    /**
     * The customer's orders, newest first (idx_o_user), with what a list row shows: the items for
     * the count and the first product image for the thumbnail.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForCustomer(int $userId, CustomerOrderFilter $filter, int $page, int $perPage): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->when($filter->status !== null, static fn ($query) => $query->where('status', $filter->status))
            ->when($filter->from !== null, static fn ($query) => $query->where('created_at', '>=', $filter->from->startOfDay()))
            ->when($filter->to !== null, static fn ($query) => $query->where('created_at', '<=', $filter->to->endOfDay()))
            ->with(self::itemRelations())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Load everything the order detail shows, in a fixed number of queries.
     */
    public function loadDetail(Order $order): Order
    {
        return $order->load([
            ...self::itemRelations(),
            'orderItems.orderItemUnits' => static fn (Relation $units) => $units->orderBy('id'),
            'orderItems.orderItemUnits.digitalCode:id,order_item_unit_id,masked_code',
            'payments' => static fn (Relation $payments) => $payments->orderBy('attempt_no'),
            'payments.paymentGateway:id,key,name',
            'refunds' => static fn (Relation $refunds) => $refunds->orderBy('id'),
            'orderEvents' => static fn (Relation $events) => $events->whereNotNull('to_status')->orderBy('id'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function itemRelations(): array
    {
        return [
            'orderItems' => static fn (Relation $items) => $items->orderBy('id'),
            'orderItems.product:id,slug',
            'orderItems.product.productImages' => static fn (Relation $images) => $images->orderBy('sort_order'),
            'orderItems.product.productImages.media',
        ];
    }
}
