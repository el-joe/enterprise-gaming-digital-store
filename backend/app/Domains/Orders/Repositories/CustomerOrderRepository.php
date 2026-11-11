<?php

namespace App\Domains\Orders\Repositories;

use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Orders\DTOs\CustomerOrderFilter;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
     * An order by public id, whoever owns it (the caller authorizes with OrderPolicy).
     */
    public function findByPublicId(string $publicId): ?Order
    {
        return Order::query()->where('public_id', $publicId)->first();
    }

    /**
     * One of the customer's own orders by public id; null for another customer's or a guest order.
     */
    public function findForCustomer(int $userId, string $publicId): ?Order
    {
        return Order::query()->where('public_id', $publicId)->where('user_id', $userId)->first(['id', 'public_id', 'order_number', 'user_id']);
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

    /**
     * Account dashboard figures (M13): all orders, orders still moving (not closed, not waiting
     * for payment), and delivered codes of paid orders that were never revealed.
     *
     * @return array{orders: int, in_progress: int, sealed_codes: int}
     */
    public function dashboardCounts(int $userId): array
    {
        $open = array_map(static fn (OrderStatus $status): string => $status->value, array_filter(
            OrderStatus::cases(),
            static fn (OrderStatus $status): bool => ! $status->isClosed() && $status !== OrderStatus::PendingPayment,
        ));

        $orders = Order::query()->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status IN ('.implode(',', array_fill(0, count($open), '?')).') THEN 1 ELSE 0 END) as open', array_values($open))
            ->toBase()
            ->first();

        return [
            'orders' => (int) ($orders->total ?? 0),
            'in_progress' => (int) ($orders->open ?? 0),
            'sealed_codes' => $this->codeUnits($userId)->where('order_item_units.status', OrderItemUnitStatus::Delivered)->whereNull('order_item_units.first_revealed_at')->count(),
        ];
    }

    /**
     * The customer's most recent orders with their items (dashboard).
     *
     * @return Collection<int, Order>
     */
    public function recentForCustomer(int $userId, int $limit): Collection
    {
        return Order::query()->where('user_id', $userId)->with(self::itemRelations())->orderByDesc('created_at')->orderByDesc('id')->limit($limit)->get();
    }

    /**
     * The newest delivered, never revealed code unit of a paid order (dashboard notice).
     */
    public function latestSealedUnit(int $userId): ?OrderItemUnit
    {
        return $this->codeUnits($userId)
            ->where('order_item_units.status', OrderItemUnitStatus::Delivered)
            ->whereNull('order_item_units.first_revealed_at')
            ->with(['order:id,public_id,order_number', 'orderItem:id,name_snapshot'])
            ->orderByDesc('order_item_units.delivered_at')
            ->orderByDesc('order_item_units.id')
            ->first();
    }

    /**
     * Digital code history (M13, spec section 9): units of the customer's paid orders that hold
     * a code, newest first. `state`: `sealed` (delivered, never revealed), `revealed`, `refunded`
     * or null for all; `search` matches the product name or the order number. Only the masked
     * code column of the code is loaded.
     *
     * @return LengthAwarePaginator<int, OrderItemUnit>
     */
    public function paginateCodeUnits(int $userId, ?string $state, ?string $search, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->codeUnits($userId)
            ->when($state === 'sealed', static fn (Builder $query) => $query->where('order_item_units.status', OrderItemUnitStatus::Delivered)->whereNull('order_item_units.first_revealed_at'))
            ->when($state === 'revealed', static fn (Builder $query) => $query->whereNotNull('order_item_units.first_revealed_at'))
            ->when($state === 'refunded', static fn (Builder $query) => $query->whereIn('order_item_units.status', [OrderItemUnitStatus::Refunded, OrderItemUnitStatus::RefundPending]))
            ->when($search !== null && $search !== '', static function (Builder $query) use ($search): void {
                $like = '%'.addcslashes((string) $search, '%_\\').'%';
                $query->where(static fn (Builder $inner) => $inner
                    ->whereHas('orderItem', static fn (Builder $item) => $item->where('name_snapshot', 'like', $like))
                    ->orWhereHas('order', static fn (Builder $order) => $order->where('order_number', 'like', $like)));
            })
            ->with([
                'order:id,public_id,order_number,user_id,primary_payment_id,paid_at,created_at',
                'orderItem:id,order_id,product_id,name_snapshot,delivery_type',
                'orderItem.product:id,slug',
                'digitalCode:id,order_item_unit_id,masked_code,expires_at',
            ])
            ->orderByDesc('order_item_units.delivered_at')
            ->orderByDesc('order_item_units.id')
            ->paginate($perPage, ['order_item_units.*'], 'page', $page);
    }

    /**
     * The customer's latest reveal attempts, granted or denied (security page).
     *
     * @return Collection<int, DigitalCodeAccess>
     */
    public function recentRevealAttempts(int $userId, int $limit): Collection
    {
        return DigitalCodeAccess::query()
            ->where('actor_type', DigitalCodeAccessActorType::Customer)
            ->where('actor_id', $userId)
            ->with(['digitalCode:id,order_item_unit_id,masked_code', 'digitalCode.orderItemUnit:id,order_item_id', 'digitalCode.orderItemUnit.orderItem:id,name_snapshot'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Builder<OrderItemUnit>
     */
    private function codeUnits(int $userId): Builder
    {
        return OrderItemUnit::query()
            ->whereHas('digitalCode')
            ->whereHas('order', static fn (Builder $order) => $order->where('user_id', $userId)->whereNotNull('primary_payment_id')->whereNotNull('paid_at'));
    }
}
