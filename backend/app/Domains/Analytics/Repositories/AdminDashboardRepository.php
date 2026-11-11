<?php

namespace App\Domains\Analytics\Repositories;

use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Counts for the admin dashboard (M14) that the analytics rollups do not hold: orders placed,
 * new customers, open orders, failed payments and payment success per gateway in a window.
 */
class AdminDashboardRepository
{
    /** Orders paid and not yet settled. */
    public const array OPEN = [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::SupplierPending, OrderStatus::PendingVerification, OrderStatus::ManualReview];

    public function ordersPlaced(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return Order::query()->whereBetween('created_at', [$from, $to])->count();
    }

    public function newCustomers(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return User::query()->where('type', UserType::Customer)->whereBetween('created_at', [$from, $to])->count();
    }

    /**
     * @return array{open: int, stuck: int}
     */
    public function openOrders(CarbonImmutable $stuckBefore): array
    {
        $open = array_map(static fn (OrderStatus $status): string => $status->value, self::OPEN);

        return [
            'open' => Order::query()->whereIn('status', $open)->count(),
            'stuck' => Order::query()->whereIn('status', $open)->where('status_changed_at', '<', $stuckBefore)->count(),
        ];
    }

    public function failedPayments(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return Payment::query()->where('status', PaymentStatus::Failed)->whereBetween('created_at', [$from, $to])->count();
    }

    /**
     * Finished attempts and successes per gateway in the window.
     *
     * @return SupportCollection<int, array{gateway: string, currency: string, finished: int, succeeded: int}>
     */
    public function gatewayOutcomes(CarbonImmutable $from, CarbonImmutable $to): SupportCollection
    {
        $succeeded = [PaymentStatus::Succeeded->value, PaymentStatus::PartiallyRefunded->value, PaymentStatus::Refunded->value];

        return Payment::query()
            ->from('payments as p')
            ->join('payment_gateways as g', 'g.id', '=', 'p.payment_gateway_id')
            ->whereBetween('p.created_at', [$from, $to])
            ->whereIn('p.status', [...$succeeded, PaymentStatus::Failed->value, PaymentStatus::Expired->value, PaymentStatus::Cancelled->value])
            ->groupBy('g.id', 'g.name', 'g.default_currency', 'g.sort_order')
            ->orderBy('g.sort_order')
            ->selectRaw('g.name as gateway, g.default_currency as currency, COUNT(*) as finished')
            ->selectRaw('SUM(CASE WHEN p.status IN ('.implode(',', array_fill(0, count($succeeded), '?')).') THEN 1 ELSE 0 END) as succeeded', $succeeded)
            ->toBase()
            ->get()
            ->map(static fn (object $row): array => ['gateway' => (string) $row->gateway, 'currency' => (string) $row->currency, 'finished' => (int) $row->finished, 'succeeded' => (int) $row->succeeded]);
    }

    /**
     * @return Collection<int, Order>
     */
    public function recentOrders(int $limit): Collection
    {
        return Order::query()->latest('id')->limit($limit)->get(['id', 'public_id', 'order_number', 'status', 'display_total', 'display_currency', 'created_at']);
    }
}
