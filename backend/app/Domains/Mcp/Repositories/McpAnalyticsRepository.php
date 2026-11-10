<?php

namespace App\Domains\Mcp\Repositories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Models\CouponRedemption;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Aggregate queries behind the MCP analytics and marketing tools. Revenue and profit come from
 * `order_item_financials` (written when an order settles and corrected on refund, M10), so an
 * order that has not settled is not in them. All SQL fragments are fixed; inputs only bind values.
 */
class McpAnalyticsRepository
{
    /**
     * Revenue, cost and profit sums per period from order_item_financials.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function financialsByPeriod(CarbonImmutable $from, CarbonImmutable $to, string $groupBy): Collection
    {
        $period = $this->periodExpression('oif.recognized_at', $groupBy);

        return OrderItemFinancial::query()
            ->from('order_item_financials as oif')
            ->join('order_items as oi', 'oi.id', '=', 'oif.order_item_id')
            ->whereBetween('oif.recognized_at', [$from, $to])
            ->selectRaw("{$period} as period")
            ->selectRaw('COUNT(DISTINCT oif.order_id) as orders_count')
            ->selectRaw('COALESCE(SUM(oi.quantity), 0) as units_count')
            ->selectRaw('COALESCE(SUM(oif.revenue_base), 0) as revenue_base')
            ->selectRaw('COALESCE(SUM(oif.refunded_base), 0) as refunded_base')
            ->selectRaw('COALESCE(SUM(oif.supplier_cost_base), 0) as supplier_cost_base')
            ->selectRaw('COALESCE(SUM(oif.gateway_fee_base), 0) as gateway_fee_base')
            ->selectRaw('COALESCE(SUM(oif.fx_cost_base), 0) as fx_cost_base')
            ->selectRaw('COALESCE(SUM(oif.other_cost_base), 0) as other_cost_base')
            ->selectRaw('COALESCE(SUM(oif.gross_profit_base), 0) as gross_profit_base')
            ->selectRaw('COALESCE(SUM(oif.net_profit_base), 0) as net_profit_base')
            ->selectRaw('COALESCE(SUM(CASE WHEN oif.gateway_fee_is_estimate THEN 1 ELSE 0 END), 0) as estimated_fee_rows')
            ->groupByRaw($period)
            ->orderByRaw($period)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Products ranked by recognised revenue.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function topProductsByRevenue(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        return OrderItemFinancial::query()
            ->from('order_item_financials as oif')
            ->join('order_items as oi', 'oi.id', '=', 'oif.order_item_id')
            ->join('products as p', 'p.id', '=', 'oif.product_id')
            ->whereBetween('oif.recognized_at', [$from, $to])
            ->groupBy('oif.product_id', 'p.public_id', 'p.sku', 'p.name')
            ->select(['p.public_id', 'p.sku', 'p.name'])
            ->selectRaw('COALESCE(SUM(oi.quantity), 0) as units_count')
            ->selectRaw('COUNT(DISTINCT oif.order_id) as orders_count')
            ->selectRaw('COALESCE(SUM(oif.revenue_base), 0) as revenue_base')
            ->selectRaw('COALESCE(SUM(oif.refunded_base), 0) as refunded_base')
            ->selectRaw('COALESCE(SUM(oif.net_profit_base), 0) as net_profit_base')
            ->orderByDesc('revenue_base')
            ->orderBy('p.sku')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Supplier attempts started in the window, by outcome, plus recognised supplier cost.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function supplierAttempts(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $costs = OrderItemFinancial::query()
            ->whereBetween('recognized_at', [$from, $to])
            ->whereNotNull('supplier_id')
            ->groupBy('supplier_id')
            ->selectRaw('supplier_id, SUM(supplier_cost_base) as supplier_cost_base')
            ->toBase()
            ->pluck('supplier_cost_base', 'supplier_id');

        return SupplierTransaction::query()
            ->from('supplier_transactions as st')
            ->join('suppliers as s', 's.id', '=', 'st.supplier_id')
            ->whereBetween('st.created_at', [$from, $to])
            ->groupBy('st.supplier_id', 's.name', 's.adapter_key')
            ->select(['st.supplier_id', 's.name', 's.adapter_key'])
            ->selectRaw('COUNT(*) as attempts')
            ->selectRaw("SUM(CASE WHEN st.status = 'succeeded' THEN 1 ELSE 0 END) as succeeded")
            ->selectRaw("SUM(CASE WHEN st.status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN st.status = 'not_accepted_verified' THEN 1 ELSE 0 END) as not_accepted_verified")
            ->selectRaw("SUM(CASE WHEN st.status IN ('submitting', 'accepted', 'pending_verification') THEN 1 ELSE 0 END) as unresolved")
            ->selectRaw('SUM(CASE WHEN st.is_fallback THEN 1 ELSE 0 END) as fallback_attempts')
            ->selectRaw("AVG(CASE WHEN st.status = 'succeeded' AND st.completed_at IS NOT NULL AND st.submitted_at IS NOT NULL THEN TIMESTAMPDIFF(SECOND, st.submitted_at, st.completed_at) END) as avg_seconds_to_success")
            ->orderBy('s.name')
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => [...(array) $row, 'supplier_cost_base' => $costs->get($row->supplier_id)]);
    }

    /**
     * Products ranked by units sold on paid orders.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function bestSellersByUnits(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        return $this->paidItems($from, $to)
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->groupBy('oi.product_id', 'p.public_id', 'p.sku', 'p.name')
            ->select(['p.public_id', 'p.sku', 'p.name'])
            ->selectRaw('SUM(oi.quantity) as units_count')
            ->selectRaw('COUNT(DISTINCT oi.order_id) as orders_count')
            ->selectRaw('SUM(oi.line_total_base) as sales_base')
            ->orderByDesc('units_count')
            ->orderBy('p.sku')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Active products with the fewest units sold on paid orders in the window (zero included).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function underperformingProducts(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        $sold = $this->paidItems($from, $to)
            ->groupBy('oi.product_id')
            ->selectRaw('oi.product_id, SUM(oi.quantity) as units_count, SUM(oi.line_total_base) as sales_base')
            ->toBase();

        return Product::query()
            ->from('products as p')
            ->leftJoinSub($sold, 'sold', 'sold.product_id', '=', 'p.id')
            ->where('p.status', 'active')
            ->whereNull('p.deleted_at')
            ->select(['p.public_id', 'p.sku', 'p.name', 'p.published_at', 'p.sales_count'])
            ->selectRaw('COALESCE(sold.units_count, 0) as units_count')
            ->selectRaw('COALESCE(sold.sales_base, 0) as sales_base')
            ->orderBy('units_count')
            ->orderBy('p.published_at')
            ->orderBy('p.id')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Coupon redemptions that became `redeemed` (paid orders) in the window.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function couponPerformance(CarbonImmutable $from, CarbonImmutable $to, ?string $code): Collection
    {
        return CouponRedemption::query()
            ->from('coupon_redemptions as cr')
            ->join('coupons as c', 'c.id', '=', 'cr.coupon_id')
            ->join('orders as o', 'o.id', '=', 'cr.order_id')
            ->where('cr.status', CouponRedemptionStatus::Redeemed)
            ->whereBetween('cr.created_at', [$from, $to])
            ->when($code !== null, static fn (Builder $query) => $query->where('c.code', mb_strtoupper((string) $code)))
            ->groupBy('cr.coupon_id', 'c.code', 'c.type', 'c.value', 'c.usage_limit', 'c.used_count', 'c.is_active', 'c.created_by_type')
            ->select(['c.code', 'c.type', 'c.value', 'c.usage_limit', 'c.used_count', 'c.is_active', 'c.created_by_type'])
            ->selectRaw('COUNT(*) as redemptions')
            ->selectRaw('SUM(cr.discount_base) as discount_base')
            ->selectRaw('SUM(o.total_base) as order_revenue_base')
            ->orderByDesc('redemptions')
            ->orderBy('c.code')
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Aggregates only: paid orders per period, registered and guest shares, repeat buyers.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function purchaseTrends(CarbonImmutable $from, CarbonImmutable $to, string $groupBy): Collection
    {
        $period = $this->periodExpression('o.paid_at', $groupBy);

        return Order::query()
            ->from('orders as o')
            ->whereNotNull('o.paid_at')
            ->whereBetween('o.paid_at', [$from, $to])
            ->selectRaw("{$period} as period")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(o.total_base) as revenue_base')
            ->selectRaw('AVG(o.total_base) as average_order_base')
            ->selectRaw('SUM(CASE WHEN o.user_id IS NULL THEN 1 ELSE 0 END) as guest_orders')
            ->selectRaw('SUM(CASE WHEN o.user_id IS NOT NULL THEN 1 ELSE 0 END) as registered_orders')
            ->selectRaw('SUM(CASE WHEN o.user_id IS NOT NULL AND EXISTS (SELECT 1 FROM orders prev WHERE prev.user_id = o.user_id AND prev.paid_at IS NOT NULL AND prev.paid_at < o.paid_at) THEN 1 ELSE 0 END) as repeat_orders')
            ->selectRaw('COUNT(DISTINCT o.user_id) as distinct_registered_buyers')
            ->groupByRaw($period)
            ->orderByRaw($period)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Paid orders per country in the window, aggregated.
     *
     * @return Collection<int, array{country_code: string, orders_count: int}>
     */
    public function paidOrdersByCountry(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        return Order::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->groupBy('country_code')
            ->selectRaw('country_code, COUNT(*) as orders_count')
            ->orderByDesc('orders_count')
            ->orderBy('country_code')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => ['country_code' => (string) $row->country_code, 'orders_count' => (int) $row->orders_count]);
    }

    /**
     * Orders placed in the window and how many of them were paid.
     *
     * @return array{placed: int, paid: int}
     */
    public function placedAndPaid(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) as placed, SUM(CASE WHEN paid_at IS NOT NULL THEN 1 ELSE 0 END) as paid')
            ->toBase()
            ->first();

        return ['placed' => (int) ($row->placed ?? 0), 'paid' => (int) ($row->paid ?? 0)];
    }

    /**
     * @return Builder<OrderItem>
     */
    private function paidItems(CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return OrderItem::query()
            ->from('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNotNull('o.paid_at')
            ->whereBetween('o.paid_at', [$from, $to])
            ->whereNotIn('o.status', [OrderStatus::Cancelled->value, OrderStatus::Refunded->value]);
    }

    private function periodExpression(string $column, string $groupBy): string
    {
        return match ($groupBy) {
            'day' => "DATE_FORMAT({$column}, '%Y-%m-%d')",
            'week' => "DATE_FORMAT({$column}, '%x-W%v')",
            'month' => "DATE_FORMAT({$column}, '%Y-%m')",
            'none' => "'all'",
            default => throw new InvalidArgumentException("Unknown grouping {$groupBy}."),
        };
    }
}
