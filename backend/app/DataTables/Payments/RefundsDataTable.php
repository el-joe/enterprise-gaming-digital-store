<?php

namespace App\DataTables\Payments;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Refund;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Refunds (spec Phase 9.6). Created from the order page (`refunds.create`); listed here.
 */
class RefundsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'refunds';
    }

    public static function permission(): string
    {
        return 'refunds.view';
    }

    public function columns(): array
    {
        return [
            new Column('created', __('admin.refunds.col.created'), 'refunds.created_at'),
            new Column('order', __('admin.refunds.col.order'), html: true),
            new Column('amount', __('admin.refunds.col.amount'), 'refunds.amount', html: true, numeric: true),
            new Column('status', __('admin.refunds.col.status'), 'refunds.status', html: true),
            new Column('reason', __('admin.refunds.col.reason'), 'refunds.reason'),
            new Column('destination', __('admin.refunds.col.destination'), 'refunds.destination'),
            new Column('requested_by', __('admin.refunds.col.requested_by'), 'refunds.requested_by_type'),
            new Column('completed', __('admin.refunds.col.completed'), 'refunds.completed_at', visible: false),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    public function filters(): array
    {
        $statuses = [];
        $reasons = [];

        foreach (RefundStatus::cases() as $status) {
            $statuses[$status->value] = Cell::label('refund_status', $status->value);
        }

        foreach (RefundReason::cases() as $reason) {
            $reasons[$reason->value] = Cell::label('refund_reason', $reason->value);
        }

        return [
            new Filter('status', __('admin.refunds.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('refunds.status', $value);
            }, $statuses),
            new Filter('reason', __('admin.refunds.col.reason'), 'select', static function (Builder $query, string $value): void {
                $query->where('refunds.reason', $value);
            }, $reasons),
        ];
    }

    protected function searchColumns(): array
    {
        return ['refunds.public_id', 'refunds.provider_refund_id'];
    }

    protected function query(): Builder
    {
        return Refund::query()->select('refunds.*')->with('order:id,public_id,order_number');
    }

    /**
     * @param  Refund  $row
     */
    protected function cells(Model $row): array
    {
        return [
            'created' => $this->dateTime($row->created_at),
            'order' => $row->order === null ? Cell::muted('—') : Cell::link(route('admin.orders.show', $row->order), (string) $row->order->order_number, mono: true),
            'amount' => Cell::strong($this->money($row->amount, $row->currency)),
            'status' => Cell::status('refund_status', $row->status?->value),
            'reason' => Cell::label('refund_reason', $row->reason?->value),
            'destination' => Cell::label('refund_destination', $row->destination?->value),
            'requested_by' => Cell::label('actor', $row->requested_by_type?->value),
            'completed' => $this->dateTime($row->completed_at),
        ];
    }
}
