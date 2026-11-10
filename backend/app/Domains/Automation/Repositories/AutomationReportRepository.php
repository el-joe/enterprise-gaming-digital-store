<?php

namespace App\Domains\Automation\Repositories;

use App\Domains\Automation\Enums\AutomationLogKind;
use App\Domains\Automation\Enums\AutomationOutboxStatus;
use App\Domains\Automation\Models\AutomationLog;
use App\Domains\Automation\Models\AutomationOutbox;
use App\Domains\Catalog\Models\Product;
use App\Domains\Mcp\Models\McpAuditLog;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Enums\RiskAssessmentDecision;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Orders\Models\RiskAssessment;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentDispute;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\ReconciliationRun;
use App\Domains\Payments\Models\Refund;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregate reads behind the automation report and monitor endpoints. Every SQL fragment is
 * fixed; inputs only bind values. Rows are returned as plain arrays without customer contact
 * details, codes or provider payloads.
 */
class AutomationReportRepository
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function ordersInStatusSince(array $statuses, CarbonImmutable $since, int $limit): Collection
    {
        return Order::query()
            ->whereIn('status', $statuses)
            ->where('status_changed_at', '>=', $since)
            ->orderByDesc('status_changed_at')
            ->limit($limit)
            ->toBase()
            ->get(['order_number', 'public_id', 'status', 'status_changed_at', 'total_base', 'base_currency', 'user_id'])
            ->map(static fn (object $row): array => (array) $row);
    }

    public function countOrdersInStatusSince(array $statuses, CarbonImmutable $since): int
    {
        return Order::query()->whereIn('status', $statuses)->where('status_changed_at', '>=', $since)->count();
    }

    /**
     * Orders that have stayed in one of the statuses since before the cut-off.
     *
     * @param  list<string>  $statuses
     * @return array{counts: array<string, int>, orders: Collection<int, array<string, mixed>>}
     */
    public function stalledOrders(array $statuses, CarbonImmutable $before, int $limit): array
    {
        $base = Order::query()->whereIn('status', $statuses)->where('status_changed_at', '<', $before);

        return [
            'counts' => (clone $base)->toBase()->groupBy('status')->selectRaw('status, COUNT(*) as aggregate')->pluck('aggregate', 'status')->map(static fn ($count): int => (int) $count)->all(),
            'orders' => $base->orderBy('status_changed_at')->limit($limit)->toBase()
                ->get(['order_number', 'public_id', 'status', 'status_changed_at'])
                ->map(static fn (object $row): array => (array) $row),
        ];
    }

    /**
     * Payment attempts created in the window per gateway, by outcome.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function paymentAttemptsByGateway(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $succeeded = [PaymentStatus::Succeeded->value, PaymentStatus::PartiallyRefunded->value, PaymentStatus::Refunded->value, PaymentStatus::DuplicateCapture->value];

        return Payment::query()
            ->from('payments as p')
            ->join('payment_gateways as g', 'g.id', '=', 'p.payment_gateway_id')
            ->whereBetween('p.created_at', [$from, $to])
            ->groupBy('g.id', 'g.key', 'g.name')
            ->select(['g.key', 'g.name'])
            ->selectRaw('COUNT(*) as attempts')
            ->selectRaw('SUM(CASE WHEN p.status IN ('.implode(',', array_fill(0, count($succeeded), '?')).') THEN 1 ELSE 0 END) as succeeded', $succeeded)
            ->selectRaw('SUM(CASE WHEN p.status = ? THEN 1 ELSE 0 END) as failed', [PaymentStatus::Failed->value])
            ->selectRaw('SUM(CASE WHEN p.status IN (?, ?) THEN 1 ELSE 0 END) as abandoned', [PaymentStatus::Expired->value, PaymentStatus::Cancelled->value])
            ->orderBy('g.key')
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Units, orders and sales on paid orders per gaming platform.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function platformsBySales(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        return $this->paidItems($from, $to)
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->join('gaming_platforms as gp', 'gp.id', '=', 'p.gaming_platform_id')
            ->groupBy('gp.id', 'gp.name', 'gp.slug')
            ->select(['gp.name', 'gp.slug'])
            ->selectRaw('SUM(oi.quantity) as units_count')
            ->selectRaw('COUNT(DISTINCT oi.order_id) as orders_count')
            ->selectRaw('SUM(oi.line_total_base) as sales_base')
            ->orderByDesc('units_count')
            ->orderBy('gp.slug')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Units sold per product in two consecutive windows of equal length.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function productMomentum(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        $previousFrom = $from->subSeconds($to->diffInSeconds($from, true));

        return $this->paidItems($previousFrom, $to)
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->join('gaming_platforms as gp', 'gp.id', '=', 'p.gaming_platform_id')
            ->groupBy('p.id', 'p.public_id', 'p.sku', 'p.name', 'gp.name')
            ->select(['p.public_id', 'p.sku', 'p.name', 'gp.name as platform'])
            ->selectRaw('SUM(CASE WHEN o.paid_at >= ? THEN oi.quantity ELSE 0 END) as units_current', [$from])
            ->selectRaw('SUM(CASE WHEN o.paid_at < ? THEN oi.quantity ELSE 0 END) as units_previous', [$from])
            ->orderByDesc('units_current')
            ->orderBy('p.sku')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Products ranked by recognised net profit.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function productsByProfit(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        return OrderItemFinancial::query()
            ->from('order_item_financials as oif')
            ->join('products as p', 'p.id', '=', 'oif.product_id')
            ->whereBetween('oif.recognized_at', [$from, $to])
            ->groupBy('p.id', 'p.public_id', 'p.sku', 'p.name')
            ->select(['p.public_id', 'p.sku', 'p.name'])
            ->selectRaw('COUNT(*) as items_count')
            ->selectRaw('SUM(oif.revenue_base) as revenue_base')
            ->selectRaw('SUM(oif.refunded_base) as refunded_base')
            ->selectRaw('SUM(oif.net_profit_base) as net_profit_base')
            ->orderByDesc('net_profit_base')
            ->orderBy('p.sku')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Recognised revenue and gateway fees per gateway.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function feesByGateway(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return OrderItemFinancial::query()
            ->from('order_item_financials as oif')
            ->leftJoin('payment_gateways as g', 'g.id', '=', 'oif.payment_gateway_id')
            ->whereBetween('oif.recognized_at', [$from, $to])
            ->groupBy('g.id', 'g.key')
            ->selectRaw("COALESCE(g.key, 'none') as gateway")
            ->selectRaw('COUNT(DISTINCT oif.order_id) as orders_count')
            ->selectRaw('SUM(oif.revenue_base) as revenue_base')
            ->selectRaw('SUM(oif.gateway_fee_base) as gateway_fee_base')
            ->selectRaw('SUM(oif.fx_cost_base) as fx_cost_base')
            ->selectRaw('SUM(CASE WHEN oif.gateway_fee_is_estimate THEN 1 ELSE 0 END) as estimated_rows')
            ->selectRaw('COUNT(*) as rows_count')
            ->orderBy('gateway')
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Items recognised in the window whose net margin is below the threshold (negative included).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function itemsBelowMargin(CarbonImmutable $from, CarbonImmutable $to, string $thresholdPct, int $limit): Collection
    {
        return OrderItemFinancial::query()
            ->from('order_item_financials as oif')
            ->join('orders as o', 'o.id', '=', 'oif.order_id')
            ->join('products as p', 'p.id', '=', 'oif.product_id')
            ->whereBetween('oif.recognized_at', [$from, $to])
            ->whereRaw('(oif.revenue_base - oif.refunded_base) > 0')
            ->whereRaw('(oif.net_profit_base * 100) < (? * (oif.revenue_base - oif.refunded_base))', [$thresholdPct])
            ->select(['o.order_number', 'p.sku', 'p.name', 'oif.revenue_base', 'oif.refunded_base', 'oif.net_profit_base', 'oif.recognized_at'])
            ->orderBy('oif.net_profit_base')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Distinct registered buyers with a paid order in the window, split into first-time and
     * returning buyers.
     *
     * @return array{buyers: int, returning: int, new: int, guest_orders: int}
     */
    public function retention(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = Order::query()
            ->from('orders as o')
            ->whereNotNull('o.paid_at')
            ->whereBetween('o.paid_at', [$from, $to])
            ->selectRaw('COUNT(DISTINCT o.user_id) as buyers')
            ->selectRaw('COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM orders prior WHERE prior.user_id = o.user_id AND prior.paid_at IS NOT NULL AND prior.paid_at < ?) THEN o.user_id END) as returning_buyers', [$from])
            ->selectRaw('SUM(CASE WHEN o.user_id IS NULL THEN 1 ELSE 0 END) as guest_orders')
            ->toBase()
            ->first();

        $buyers = (int) ($row->buyers ?? 0);
        $returning = (int) ($row->returning_buyers ?? 0);

        return ['buyers' => $buyers, 'returning' => $returning, 'new' => $buyers - $returning, 'guest_orders' => (int) ($row->guest_orders ?? 0)];
    }

    /**
     * Risk assessments that held or blocked an order in the window.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function riskFlags(CarbonImmutable $from, CarbonImmutable $to, int $limit): Collection
    {
        return RiskAssessment::query()
            ->from('risk_assessments as ra')
            ->join('orders as o', 'o.id', '=', 'ra.order_id')
            ->whereIn('ra.decision', [RiskAssessmentDecision::Review->value, RiskAssessmentDecision::Block->value])
            ->whereBetween('ra.created_at', [$from, $to])
            ->orderByDesc('ra.score')
            ->limit($limit)
            ->toBase()
            ->get(['o.order_number', 'o.status', 'o.total_base', 'ra.score', 'ra.decision', 'ra.signals', 'ra.created_at'])
            ->map(static fn (object $row): array => (array) $row);
    }

    public function disputesOpened(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return PaymentDispute::query()->whereBetween('created_at', [$from, $to])->count();
    }

    /**
     * Refunds created in the window by status and reason.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function refundsByStatus(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Refund::query()
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status', 'reason', 'destination')
            ->select(['status', 'reason', 'destination'])
            ->selectRaw('COUNT(*) as refunds_count')
            ->selectRaw('SUM(amount_base) as amount_base')
            ->orderBy('status')
            ->orderBy('reason')
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * The latest daily payment reconciliation runs and the open items they left.
     *
     * @return array{runs: Collection<int, array<string, mixed>>, open_items: array<string, int>}
     */
    public function reconciliation(CarbonImmutable $since): array
    {
        return [
            'runs' => ReconciliationRun::query()
                ->where('started_at', '>=', $since)
                ->orderByDesc('started_at')
                ->limit(50)
                ->toBase()
                ->get(['kind', 'subject_key', 'status', 'checked', 'mismatches', 'window_start', 'window_end', 'started_at', 'finished_at'])
                ->map(static fn (object $row): array => (array) $row),
            'open_items' => ReconciliationItem::query()
                ->where('status', ReconciliationItemStatus::Open)
                ->toBase()
                ->groupBy('type')
                ->selectRaw('type, COUNT(*) as aggregate')
                ->pluck('aggregate', 'type')
                ->map(static fn ($count): int => (int) $count)
                ->all(),
        ];
    }

    /**
     * @return array{pending_by_queue: array<string, array{jobs: int, oldest_available_at: ?int}>, failed_total: int, failed_since: array<string, int>}
     */
    public function queues(CarbonImmutable $since): array
    {
        $pending = DB::table('jobs')
            ->groupBy('queue')
            ->selectRaw('queue, COUNT(*) as jobs, MIN(available_at) as oldest_available_at')
            ->get()
            ->mapWithKeys(static fn (object $row): array => [(string) $row->queue => ['jobs' => (int) $row->jobs, 'oldest_available_at' => $row->oldest_available_at === null ? null : (int) $row->oldest_available_at]])
            ->all();

        return [
            'pending_by_queue' => $pending,
            'failed_total' => DB::table('failed_jobs')->count(),
            'failed_since' => DB::table('failed_jobs')
                ->where('failed_at', '>=', $since)
                ->groupBy('queue')
                ->selectRaw('queue, COUNT(*) as aggregate')
                ->pluck('aggregate', 'queue')
                ->map(static fn ($count): int => (int) $count)
                ->all(),
        ];
    }

    /**
     * Inbound payment webhooks that failed or had a bad signature, and the outbox state.
     *
     * @return array<string, int>
     */
    public function webhookFailures(CarbonImmutable $since): array
    {
        return [
            'payment_webhooks_failed' => PaymentWebhook::query()->where('status', WebhookStatus::Failed)->where('created_at', '>=', $since)->count(),
            'payment_webhooks_bad_signature' => PaymentWebhook::query()->where('signature_valid', false)->where('created_at', '>=', $since)->count(),
            'outbox_failed' => AutomationOutbox::query()->where('status', AutomationOutboxStatus::Failed)->where('failed_at', '>=', $since)->count(),
            'outbox_failed_total' => AutomationOutbox::query()->where('status', AutomationOutboxStatus::Failed)->count(),
            'outbox_retrying' => AutomationOutbox::query()->where('status', AutomationOutboxStatus::Pending)->where('attempts', '>', 0)->count(),
            'outbox_pending' => AutomationOutbox::query()->where('status', AutomationOutboxStatus::Pending)->count(),
        ];
    }

    /**
     * Average and maximum durations recorded for calls in the window.
     *
     * @return array<string, array{samples: int, avg_ms: ?int, max_ms: ?int}>
     */
    public function recordedLatency(CarbonImmutable $since): array
    {
        $summarise = static fn (Builder $query, string $column): array => (static function (?object $row): array {
            return [
                'samples' => (int) ($row->samples ?? 0),
                'avg_ms' => $row?->avg_ms === null ? null : (int) round((float) $row->avg_ms),
                'max_ms' => $row?->max_ms === null ? null : (int) $row->max_ms,
            ];
        })($query->toBase()->selectRaw("COUNT({$column}) as samples, AVG({$column}) as avg_ms, MAX({$column}) as max_ms")->first());

        return [
            'supplier_health_checks' => $summarise(SupplierHealthCheck::query()->where('created_at', '>=', $since)->whereNotNull('latency_ms'), 'latency_ms'),
            'automation_api' => $summarise(AutomationLog::query()->where('kind', AutomationLogKind::Call)->where('started_at', '>=', $since), 'duration_ms'),
            'mcp_api' => $summarise(McpAuditLog::query()->where('created_at', '>=', $since), 'duration_ms'),
        ];
    }

    /**
     * Active products without a description or short description.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function productsMissingDescriptions(int $limit): Collection
    {
        return Product::query()
            ->from('products as p')
            ->join('gaming_platforms as gp', 'gp.id', '=', 'p.gaming_platform_id')
            ->join('categories as c', 'c.id', '=', 'p.primary_category_id')
            ->where('p.status', 'active')
            ->whereNull('p.deleted_at')
            ->where(static fn ($missing) => $missing->whereNull('p.description')->orWhere('p.description', '')->orWhereNull('p.short_description')->orWhere('p.short_description', ''))
            ->orderByDesc('p.sales_count')
            ->orderBy('p.id')
            ->limit($limit)
            ->toBase()
            ->get(['p.public_id', 'p.sku', 'p.name', 'p.delivery_type', 'gp.name as platform', 'c.name as category'])
            ->map(static fn (object $row): array => (array) $row);
    }

    public function findProductForContext(string $publicId): ?Product
    {
        return Product::query()
            ->with(['gamingPlatform:id,name', 'primaryCategory:id,name', 'productVariants:id,product_id,sku,title,denomination_value,denomination_currency,price_base,availability,is_active'])
            ->where('public_id', $publicId)
            ->first();
    }

    /**
     * @return array{units: int, orders: int}
     */
    public function productSales(int $productId, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = $this->paidItems($from, $to)->where('oi.product_id', $productId)
            ->selectRaw('COALESCE(SUM(oi.quantity), 0) as units, COUNT(DISTINCT oi.order_id) as orders')
            ->toBase()
            ->first();

        return ['units' => (int) ($row->units ?? 0), 'orders' => (int) ($row->orders ?? 0)];
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
}
