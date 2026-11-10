<?php

namespace App\Domains\Mcp\Repositories;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only order queries behind the MCP orders, analytics and operations tools. Customer
 * identifiers are masked by the resources; delivery values and digital codes are never loaded.
 */
class McpOrderRepository
{
    private const array LIST_COLUMNS = ['id', 'public_id', 'order_number', 'user_id', 'guest_email', 'contact_email', 'status', 'status_changed_at', 'country_code', 'base_currency', 'total_base', 'display_currency', 'display_total', 'payment_currency', 'payment_total', 'payment_gateway_id', 'coupon_code', 'paid_at', 'completed_at', 'created_at'];

    /**
     * @param  list<OrderStatus>  $statuses
     * @return LengthAwarePaginator<int, Order>
     */
    public function ordersPage(array $statuses, ?CarbonImmutable $from, ?CarbonImmutable $to, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->listQuery()
            ->when($statuses !== [], static fn (Builder $query) => $query->whereIn('status', $statuses))
            ->when($from, static fn (Builder $query, CarbonImmutable $date) => $query->where('created_at', '>=', $date))
            ->when($to, static fn (Builder $query, CarbonImmutable $date) => $query->where('created_at', '<', $date))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * Orders by exact contact or guest email (case-insensitive), or one exact order number.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function customerOrdersPage(?string $email, ?string $orderNumber, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->listQuery()
            ->when($email !== null, static fn (Builder $query) => $query->where(static fn (Builder $match) => $match
                ->where('contact_email', mb_strtolower((string) $email))
                ->orWhere('guest_email', mb_strtolower((string) $email))))
            ->when($orderNumber !== null, static fn (Builder $query) => $query->where('order_number', $orderNumber))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * Orders waiting on the store: paid or in fulfilment, unchanged for at least the given time.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function stalledOrdersPage(int $olderThanMinutes, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->listQuery()
            ->whereIn('status', self::waitingStatuses())
            ->where('status_changed_at', '<=', now()->subMinutes($olderThanMinutes))
            ->orderBy('status_changed_at')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }

    public function findByNumber(string $orderNumber): ?Order
    {
        return Order::query()
            ->with([
                'paymentGateway:id,key',
                'orderItems:id,order_id,product_id,product_variant_id,name_snapshot,sku_snapshot,delivery_type,quantity,unit_price_base,discount_base,line_total_base',
                'orderItemUnits:id,public_id,order_id,order_item_id,status,delivered_at,refunded_at,updated_at',
                'orderItemUnits.supplierTransactions' => static fn ($query) => $query
                    ->select(['id', 'public_id', 'order_item_unit_id', 'supplier_id', 'attempt_no', 'status', 'outcome_reason', 'is_fallback', 'submitted_at', 'completed_at'])
                    ->orderBy('attempt_no'),
            ])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function findStatusByNumber(string $orderNumber): ?Order
    {
        return Order::query()->select(self::LIST_COLUMNS)->where('order_number', $orderNumber)->first();
    }

    public function findUnit(Order $order, string $unitPublicId): ?OrderItemUnit
    {
        return OrderItemUnit::query()->where('order_id', $order->id)->where('public_id', $unitPublicId)->first();
    }

    public function latestSupplierTransaction(OrderItemUnit $unit): ?SupplierTransaction
    {
        return SupplierTransaction::query()->where('order_item_unit_id', $unit->id)->orderByDesc('attempt_no')->orderByDesc('id')->first();
    }

    /**
     * Row locks in the order the fulfilment service takes them (order, then unit).
     */
    public function lockOrder(Order $order): void
    {
        Order::query()->whereKey($order->id)->lockForUpdate()->value('id');
    }

    public function lockUnit(OrderItemUnit $unit): void
    {
        OrderItemUnit::query()->whereKey($unit->id)->lockForUpdate()->value('id');
    }

    /**
     * @return list<OrderStatus>
     */
    public static function waitingStatuses(): array
    {
        return [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::SupplierPending, OrderStatus::PendingVerification, OrderStatus::ManualReview, OrderStatus::RefundPending];
    }

    /**
     * @return Builder<Order>
     */
    private function listQuery(): Builder
    {
        return Order::query()->select(self::LIST_COLUMNS)->with('paymentGateway:id,key')->withCount('orderItemUnits');
    }
}
