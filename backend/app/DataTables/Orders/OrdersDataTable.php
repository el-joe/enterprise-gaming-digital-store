<?php

namespace App\DataTables\Orders;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Mcp\Services\PiiMasker;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * All orders (spec Phase 9.4). The customer's email is masked unless the viewer holds
 * `customers.view_pii`, and only then is it searchable.
 */
class OrdersDataTable extends AdminDataTable
{
    /** Statuses that need a person (the navigation badge and the "needs action" filter). */
    public const array ATTENTION = [OrderStatus::ManualReview, OrderStatus::PendingVerification, OrderStatus::PartiallyFulfilled];

    public static function key(): string
    {
        return 'orders';
    }

    public static function permission(): string
    {
        return 'orders.view';
    }

    public function columns(): array
    {
        return [
            new Column('number', __('admin.orders.col.number'), 'orders.order_number', html: true),
            new Column('customer', __('admin.orders.col.customer'), 'orders.customer_name', html: true),
            new Column('status', __('admin.orders.col.status'), 'orders.status', html: true),
            new Column('total', __('admin.orders.col.total'), 'orders.total_base', html: true, numeric: true),
            new Column('charged', __('admin.orders.col.charged'), 'orders.payment_total', numeric: true),
            new Column('gateway', __('admin.orders.col.gateway')),
            new Column('risk', __('admin.orders.col.risk'), 'orders.risk_score', numeric: true, visible: false),
            new Column('country', __('admin.orders.col.country'), 'orders.country_code', visible: false),
            new Column('placed', __('admin.orders.col.placed'), 'orders.created_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['placed', 'desc'];
    }

    public function filters(): array
    {
        $statuses = [];

        foreach (OrderStatus::cases() as $status) {
            $statuses[$status->value] = Cell::label('order_status', $status->value);
        }

        return [
            new Filter('status', __('admin.orders.filter.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('orders.status', $value);
            }, $statuses),
            new Filter('attention', __('admin.orders.filter.attention'), 'select', static function (Builder $query): void {
                $query->whereIn('orders.status', array_map(static fn (OrderStatus $status): string => $status->value, self::ATTENTION));
            }, ['1' => __('admin.orders.filter.attention_only')]),
            new Filter('gateway', __('admin.orders.filter.gateway'), 'select', static function (Builder $query, string $value): void {
                $query->where('orders.payment_gateway_id', (int) $value);
            }, PaymentGateway::query()->orderBy('sort_order')->pluck('name', 'id')->mapWithKeys(static fn (string $name, int $id): array => [(string) $id => $name])->all()),
            new Filter('from', __('admin.table.date_from'), 'date', static function (Builder $query, string $value): void {
                $query->where('orders.created_at', '>=', $value.' 00:00:00');
            }),
            new Filter('to', __('admin.table.date_to'), 'date', static function (Builder $query, string $value): void {
                $query->where('orders.created_at', '<=', $value.' 23:59:59');
            }),
        ];
    }

    protected function searchColumns(): array
    {
        return $this->viewer->can('customers.view_pii')
            ? ['orders.order_number', 'orders.customer_name', 'orders.contact_email']
            : ['orders.order_number', 'orders.customer_name'];
    }

    public function bulkActions(): array
    {
        if (! $this->viewer->can('orders.reconcile')) {
            return [];
        }

        return [[
            'key' => 'reconcile',
            'label' => __('admin.orders.bulk.reconcile'),
            'url' => route('admin.api.tables.orders.bulk.reconcile'),
            'danger' => false,
            'confirm' => __('admin.orders.bulk.reconcile_confirm'),
        ]];
    }

    public function rowId(Model $row): string
    {
        return (string) $row->getAttribute('public_id');
    }

    protected function idColumn(): string
    {
        return 'orders.public_id';
    }

    protected function query(): Builder
    {
        return Order::query()
            ->select('orders.*')
            ->with(['paymentGateway:id,name', 'user:id,name,email']);
    }

    /**
     * @param  Order  $row
     */
    protected function cells(Model $row): array
    {
        $name = $row->customer_name ?? $row->user?->name ?? __('admin.orders.guest');
        $email = $row->contact_email ?? $row->user?->email;
        $email = $this->viewer->can('customers.view_pii') ? $email : app(PiiMasker::class)->email($email);

        return [
            'number' => Cell::link(route('admin.orders.show', $row), (string) $row->order_number, mono: true),
            'customer' => Cell::stack((string) $name, $email),
            'status' => Cell::status('order_status', $row->status->value),
            'total' => Cell::strong($this->money($row->display_total, $row->display_currency)),
            'charged' => $this->money($row->payment_total, $row->payment_currency),
            'gateway' => (string) ($row->paymentGateway?->name ?? '—'),
            'risk' => $row->risk_score === null ? '—' : (string) $row->risk_score,
            'country' => (string) ($row->country_code ?? '—'),
            'placed' => $this->dateTime($row->created_at),
        ];
    }
}
