<?php

namespace App\DataTables\Payments;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Payments\Enums\PaymentDisputeStatus;
use App\Domains\Payments\Models\PaymentDispute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Payment disputes recorded from provider webhooks (M10). Read only: evidence is submitted in
 * the provider's dashboard.
 */
class DisputesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'disputes';
    }

    public static function permission(): string
    {
        return 'payments.view';
    }

    public function columns(): array
    {
        return [
            new Column('opened', __('admin.disputes.col.opened'), 'payment_disputes.created_at'),
            new Column('dispute', __('admin.disputes.col.dispute'), html: true),
            new Column('order', __('admin.disputes.col.order'), html: true),
            new Column('amount', __('admin.disputes.col.amount'), 'payment_disputes.amount', html: true, numeric: true),
            new Column('status', __('admin.disputes.col.status'), 'payment_disputes.status', html: true),
            new Column('reason', __('admin.disputes.col.reason')),
            new Column('due', __('admin.disputes.col.due'), 'payment_disputes.evidence_due_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['opened', 'desc'];
    }

    public function filters(): array
    {
        $statuses = [];

        foreach (PaymentDisputeStatus::cases() as $status) {
            $statuses[$status->value] = Cell::label('dispute_status', $status->value);
        }

        return [
            new Filter('status', __('admin.disputes.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('payment_disputes.status', $value);
            }, $statuses),
        ];
    }

    protected function searchColumns(): array
    {
        return ['payment_disputes.provider_dispute_id'];
    }

    protected function query(): Builder
    {
        return PaymentDispute::query()->select('payment_disputes.*')->with('payment:id,order_id', 'payment.order:id,public_id,order_number');
    }

    /**
     * @param  PaymentDispute  $row
     */
    protected function cells(Model $row): array
    {
        $order = $row->payment?->order;

        return [
            'opened' => $this->dateTime($row->created_at),
            'dispute' => Cell::mono(PaymentReference::short($row->provider_dispute_id)),
            'order' => $order === null ? Cell::muted('—') : Cell::link(route('admin.orders.show', $order), (string) $order->order_number, mono: true),
            'amount' => Cell::strong($this->money($row->amount, $row->currency)),
            'status' => Cell::status('dispute_status', $row->status?->value),
            'reason' => (string) ($row->reason ?? '—'),
            'due' => $this->dateTime($row->evidence_due_at),
        ];
    }
}
