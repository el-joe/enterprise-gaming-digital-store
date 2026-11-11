<?php

namespace App\DataTables\Payments;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Payment webhook log (spec Phase 9.6). The encrypted payload and the headers are never read
 * here: only the event, signature result, processing status and error.
 */
class WebhooksDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'webhooks';
    }

    public static function permission(): string
    {
        return 'payments.webhook_logs';
    }

    public function columns(): array
    {
        return [
            new Column('received', __('admin.webhooks.col.received'), 'payment_webhooks.created_at'),
            new Column('gateway', __('admin.webhooks.col.gateway')),
            new Column('event', __('admin.webhooks.col.event'), 'payment_webhooks.event_type', html: true),
            new Column('event_id', __('admin.webhooks.col.event_id'), html: true),
            new Column('signature', __('admin.webhooks.col.signature'), 'payment_webhooks.signature_valid', html: true),
            new Column('status', __('admin.webhooks.col.status'), 'payment_webhooks.status', html: true),
            new Column('attempts', __('admin.webhooks.col.attempts'), 'payment_webhooks.attempts', numeric: true),
            new Column('error', __('admin.webhooks.col.error'), visible: false),
            new Column('processed', __('admin.webhooks.col.processed'), 'payment_webhooks.processed_at', visible: false),
        ];
    }

    public function defaultOrder(): array
    {
        return ['received', 'desc'];
    }

    public function filters(): array
    {
        $statuses = [];

        foreach (WebhookStatus::cases() as $status) {
            $statuses[$status->value] = Cell::label('webhook_status', $status->value);
        }

        return [
            new Filter('gateway', __('admin.webhooks.col.gateway'), 'select', static function (Builder $query, string $value): void {
                $query->where('payment_webhooks.payment_gateway_id', (int) $value);
            }, PaymentGateway::query()->orderBy('sort_order')->pluck('name', 'id')->mapWithKeys(static fn (string $name, int $id): array => [(string) $id => $name])->all()),
            new Filter('status', __('admin.webhooks.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('payment_webhooks.status', $value);
            }, $statuses),
            new Filter('signature', __('admin.webhooks.col.signature'), 'select', static function (Builder $query, string $value): void {
                $query->where('payment_webhooks.signature_valid', $value === 'valid');
            }, ['valid' => __('admin.webhooks.valid'), 'invalid' => __('admin.webhooks.invalid')]),
        ];
    }

    protected function searchColumns(): array
    {
        return ['payment_webhooks.event_type', 'payment_webhooks.provider_event_id'];
    }

    protected function query(): Builder
    {
        return PaymentWebhook::query()
            ->select(['payment_webhooks.id', 'payment_webhooks.payment_gateway_id', 'payment_webhooks.provider_event_id', 'payment_webhooks.event_type', 'payment_webhooks.signature_valid', 'payment_webhooks.status', 'payment_webhooks.attempts', 'payment_webhooks.error', 'payment_webhooks.processed_at', 'payment_webhooks.created_at'])
            ->with('paymentGateway:id,name');
    }

    /**
     * @param  PaymentWebhook  $row
     */
    protected function cells(Model $row): array
    {
        return [
            'received' => $this->dateTime($row->created_at),
            'gateway' => (string) ($row->paymentGateway?->name ?? '—'),
            'event' => Cell::mono($row->event_type),
            'event_id' => Cell::mono(PaymentReference::short($row->provider_event_id)),
            'signature' => $row->signature_valid ? Cell::badge(__('admin.webhooks.valid'), 'success', false) : Cell::badge(__('admin.webhooks.invalid'), 'danger', false),
            'status' => Cell::status('webhook_status', $row->status?->value),
            'attempts' => (string) $row->attempts,
            'error' => mb_substr((string) ($row->error ?? '—'), 0, 120),
            'processed' => $this->dateTime($row->processed_at),
        ];
    }
}
