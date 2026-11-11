<?php

namespace App\DataTables\Support;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Support\Enums\RefundRequestStatus;
use App\Domains\Support\Models\RefundRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Customer refund requests (spec Phase 9.10): review, then approve through the refund flow or reject with a note.
 */
class RefundRequestsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'refund-requests';
    }

    public static function permission(): string
    {
        return 'support.refund_requests';
    }

    public function columns(): array
    {
        return [
            new Column('request', __('admin.col.request'), html: true),
            new Column('order', __('admin.col.order'), html: true),
            new Column('customer', __('admin.col.customer'), html: true),
            new Column('reason', __('admin.col.reason')),
            new Column('status', __('admin.col.status'), 'refund_requests.status', html: true),
            new Column('created', __('admin.col.created'), 'refund_requests.created_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    public function filters(): array
    {
        return [new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
            $query->where('refund_requests.status', $value);
        }, $this->enumOptions('refund_request_status', RefundRequestStatus::cases()))];
    }

    protected function query(): Builder
    {
        return RefundRequest::query()->select('refund_requests.*')->with(['order:id,public_id,order_number', 'user:id,name']);
    }

    /**
     * @param  RefundRequest  $row
     */
    protected function cells(Model $row): array
    {

        return [
            'request' => Cell::link(route('admin.support.refund-requests.show', $row->id), '#'.$row->id, true),
            'order' => e((string) ($row->order?->order_number ?? '—')),
            'customer' => e((string) ($row->user?->name ?? '—')),
            'reason' => Str::limit((string) $row->reason, 80),
            'status' => Cell::status('refund_request_status', $row->status?->value),
            'created' => $this->dateTime($row->created_at),
        ];
    }
}
