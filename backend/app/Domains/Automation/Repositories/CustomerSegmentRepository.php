<?php

namespace App\Domains\Automation\Repositories;

use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Customers\Services\CustomerContactPolicy;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * Recipient segments for the scheduled customer workflows (D06 to D09). Every segment is filtered
 * by CustomerContactPolicy for marketing on the channel, and excludes customers already sent the
 * same message (automation_customer_messages, by dedupe key). Contact details are returned only
 * for eligible customers.
 */
class CustomerSegmentRepository
{
    public function __construct(private CustomerContactPolicy $policy) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function abandonedCarts(CarbonImmutable $inactiveSince, CarbonImmutable $notBefore, NotificationPreferenceChannel $channel, int $limit): Collection
    {
        $query = Cart::query()
            ->from('carts as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.status', CartStatus::Active->value)
            ->whereNull('c.reminder_sent_at')
            ->whereBetween('c.last_activity_at', [$notBefore, $inactiveSince])
            ->whereExists(static fn (QueryBuilder $items) => $items->selectRaw('1')->from('cart_items as ci')->whereColumn('ci.cart_id', 'c.id'))
            ->whereNotExists($this->alreadySent("CONCAT('D06:cart:', c.public_id)"));

        return $this->policy->eligible($query, NotificationPreferenceCategory::Marketing, $channel, 'u')
            ->orderBy('c.last_activity_at')
            ->limit($limit)
            ->toBase()
            ->get(['c.public_id as cart_id', 'u.public_id as customer_id', 'u.phone', 'u.locale'])
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Items of completed orders delivered within the window that the customer has not reviewed.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function reviewEligible(CarbonImmutable $from, CarbonImmutable $to, NotificationPreferenceChannel $channel, int $limit): Collection
    {
        $query = OrderItem::query()
            ->from('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->where('o.status', OrderStatus::Completed->value)
            ->whereBetween('o.completed_at', [$from, $to])
            ->whereNotExists(static fn (QueryBuilder $reviews) => $reviews->selectRaw('1')->from('reviews as r')->whereColumn('r.order_item_id', 'oi.id')->whereColumn('r.user_id', 'o.user_id'))
            ->whereNotExists($this->alreadySent("CONCAT('D07:review:', oi.id)"));

        return $this->policy->eligible($query, NotificationPreferenceCategory::Marketing, $channel, 'u')
            ->orderBy('o.completed_at')
            ->limit($limit)
            ->toBase()
            ->get(['oi.id as order_item_id', 'o.public_id as order_id', 'o.order_number', 'p.name as product_name', 'u.public_id as customer_id', 'u.phone', 'u.locale'])
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Customers whose last order falls between the two dates, once per period.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function reactivation(CarbonImmutable $lastOrderFrom, CarbonImmutable $lastOrderTo, string $period, NotificationPreferenceChannel $channel, int $limit): Collection
    {
        $query = CustomerProfile::query()
            ->from('customer_profiles as cp')
            ->join('users as u', 'u.id', '=', 'cp.user_id')
            ->whereBetween('cp.last_order_at', [$lastOrderFrom, $lastOrderTo])
            ->whereNotExists($this->alreadySent("CONCAT('D08:reactivation:', u.public_id, ':', ?)", [$period]));

        return $this->policy->eligible($query, NotificationPreferenceCategory::Marketing, $channel, 'u')
            ->orderBy('cp.last_order_at')
            ->limit($limit)
            ->toBase()
            ->get(['u.public_id as customer_id', 'u.phone', 'u.locale', 'cp.last_order_at'])
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Customers with a paid order who have not had recommendations this period.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function recommendationRecipients(string $period, NotificationPreferenceChannel $channel, int $limit): Collection
    {
        $query = User::query()
            ->from('users as u')
            ->whereExists(static fn (QueryBuilder $orders) => $orders->selectRaw('1')->from('orders as o')->whereColumn('o.user_id', 'u.id')->whereNotNull('o.paid_at'))
            ->whereNotExists($this->alreadySent("CONCAT('D09:reco:', u.public_id, ':', ?)", [$period]));

        return $this->policy->eligible($query, NotificationPreferenceCategory::Marketing, $channel, 'u')
            ->orderBy('u.id')
            ->limit($limit)
            ->toBase()
            ->get(['u.id', 'u.public_id as customer_id', 'u.phone', 'u.locale'])
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Products each customer bought on paid orders, newest first, with the product's platform.
     *
     * @param  list<int>  $userIds
     * @return Collection<int, array<string, mixed>>
     */
    public function purchasedProducts(array $userIds): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return OrderItem::query()
            ->from('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->whereIn('o.user_id', $userIds)
            ->whereNotNull('o.paid_at')
            ->orderByDesc('o.paid_at')
            ->toBase()
            ->get(['o.user_id', 'oi.product_id', 'p.gaming_platform_id'])
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * Best-selling active products per platform on paid orders since the date.
     *
     * @param  list<int>  $platformIds
     * @return Collection<int, array<string, mixed>>
     */
    public function bestSellersByPlatform(array $platformIds, CarbonImmutable $since, int $perPlatform): Collection
    {
        if ($platformIds === []) {
            return collect();
        }

        return OrderItem::query()
            ->from('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->join('gaming_platforms as gp', 'gp.id', '=', 'p.gaming_platform_id')
            ->whereIn('p.gaming_platform_id', $platformIds)
            ->where('p.status', 'active')
            ->whereNull('p.deleted_at')
            ->whereNotNull('o.paid_at')
            ->where('o.paid_at', '>=', $since)
            ->groupBy('p.id', 'p.name', 'p.slug', 'p.gaming_platform_id', 'gp.name')
            ->select(['p.id as product_id', 'p.name', 'p.slug', 'p.gaming_platform_id', 'gp.name as platform'])
            ->selectRaw('SUM(oi.quantity) as units')
            ->orderByDesc('units')
            ->orderBy('p.id')
            ->limit(count($platformIds) * ($perPlatform + 20))
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => (array) $row);
    }

    /**
     * @param  list<mixed>  $bindings
     * @return \Closure(QueryBuilder): QueryBuilder
     */
    private function alreadySent(string $dedupeExpression, array $bindings = []): \Closure
    {
        return static fn (QueryBuilder $messages): QueryBuilder => $messages->selectRaw('1')
            ->from('automation_customer_messages as acm')
            ->whereRaw("acm.dedupe_key = {$dedupeExpression}", $bindings)
            ->where('acm.status', 'sent');
    }
}
