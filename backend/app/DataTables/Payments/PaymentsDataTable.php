<?php

namespace App\DataTables\Payments;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Payment attempts (spec Phase 9.6 "Transaction history", "Failed payments"). The provider
 * reference is shortened; no card data or provider secret is stored, so none is shown.
 */
class PaymentsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'payments';
    }

    public static function permission(): string
    {
        return 'payments.view';
    }

    public function columns(): array
    {
        return [
            new Column('reference', __('admin.payments.col.reference'), 'payments.provider_payment_id', html: true),
            new Column('order', __('admin.payments.col.order'), html: true),
            new Column('gateway', __('admin.payments.col.gateway')),
            new Column('amount', __('admin.payments.col.amount'), 'payments.amount', html: true, numeric: true),
            new Column('status', __('admin.payments.col.status'), 'payments.status', html: true),
            new Column('attempt', __('admin.payments.col.attempt'), 'payments.attempt_no', numeric: true, visible: false),
            new Column('failure', __('admin.payments.col.failure'), visible: false),
            new Column('created', __('admin.payments.col.created'), 'payments.created_at'),
            new Column('verified', __('admin.payments.col.verified'), 'payments.verified_at', visible: false),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    public function filters(): array
    {
        $statuses = [];

        foreach (PaymentStatus::cases() as $status) {
            $statuses[$status->value] = Cell::label('payment_status', $status->value);
        }

        return [
            new Filter('gateway', __('admin.payments.col.gateway'), 'select', static function (Builder $query, string $value): void {
                $query->where('payments.payment_gateway_id', (int) $value);
            }, PaymentGateway::query()->orderBy('sort_order')->pluck('name', 'id')->mapWithKeys(static fn (string $name, int $id): array => [(string) $id => $name])->all()),
            new Filter('status', __('admin.payments.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('payments.status', $value);
            }, $statuses),
            new Filter('from', __('admin.table.date_from'), 'date', static function (Builder $query, string $value): void {
                $query->where('payments.created_at', '>=', $value.' 00:00:00');
            }),
            new Filter('to', __('admin.table.date_to'), 'date', static function (Builder $query, string $value): void {
                $query->where('payments.created_at', '<=', $value.' 23:59:59');
            }),
        ];
    }

    protected function searchColumns(): array
    {
        return ['payments.provider_payment_id', 'payments.provider_order_id', 'payments.public_id'];
    }

    protected function query(): Builder
    {
        return Payment::query()->select('payments.*')->with(['paymentGateway:id,name', 'order:id,public_id,order_number']);
    }

    /**
     * @param  Payment  $row
     */
    protected function cells(Model $row): array
    {
        return [
            'reference' => Cell::mono(PaymentReference::short($row->provider_payment_id ?? $row->provider_order_id)),
            'order' => $row->order === null ? Cell::muted('—') : Cell::link(route('admin.orders.show', $row->order), (string) $row->order->order_number, mono: true),
            'gateway' => (string) ($row->paymentGateway?->name ?? '—'),
            'amount' => Cell::strong($this->money($row->amount, $row->currency)),
            'status' => Cell::status('payment_status', $row->status?->value),
            'attempt' => (string) $row->attempt_no,
            'failure' => (string) ($row->failure_code ?? '—'),
            'created' => $this->dateTime($row->created_at),
            'verified' => $this->dateTime($row->verified_at),
        ];
    }
}
