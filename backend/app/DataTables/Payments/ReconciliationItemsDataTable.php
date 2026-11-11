<?php

namespace App\DataTables\Payments;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Enums\ReconciliationItemType;
use App\Domains\Payments\Models\ReconciliationItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Differences found by `payments:reconcile-daily` (spec Phase 9.6 "Reconciliation"). Staff with
 * `payments.manage` resolve or ignore them with a note.
 */
class ReconciliationItemsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'reconciliation';
    }

    public static function permission(): string
    {
        return 'payments.view';
    }

    public function columns(): array
    {
        return [
            new Column('found', __('admin.reconciliation.col.found'), 'reconciliation_items.created_at'),
            new Column('run', __('admin.reconciliation.col.run'), html: true),
            new Column('type', __('admin.reconciliation.col.type'), 'reconciliation_items.type', html: true),
            new Column('reference', __('admin.reconciliation.col.reference'), html: true),
            new Column('status', __('admin.reconciliation.col.status'), 'reconciliation_items.status', html: true),
            new Column('note', __('admin.reconciliation.col.note'), visible: false),
        ];
    }

    public function defaultOrder(): array
    {
        return ['found', 'desc'];
    }

    public function filters(): array
    {
        $statuses = [];
        $types = [];

        foreach (ReconciliationItemStatus::cases() as $status) {
            $statuses[$status->value] = Cell::label('reconciliation_status', $status->value);
        }

        foreach (ReconciliationItemType::cases() as $type) {
            $types[$type->value] = Cell::label('reconciliation_type', $type->value);
        }

        return [
            new Filter('status', __('admin.reconciliation.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('reconciliation_items.status', $value);
            }, $statuses),
            new Filter('type', __('admin.reconciliation.col.type'), 'select', static function (Builder $query, string $value): void {
                $query->where('reconciliation_items.type', $value);
            }, $types),
        ];
    }

    protected function searchColumns(): array
    {
        return ['reconciliation_items.provider_reference'];
    }

    public function bulkActions(): array
    {
        if (! $this->viewer->can('payments.manage')) {
            return [];
        }

        return [
            ['key' => 'resolve', 'label' => __('admin.reconciliation.bulk.resolve'), 'url' => route('admin.api.tables.reconciliation.bulk.resolve'), 'danger' => false, 'confirm' => __('admin.reconciliation.bulk.resolve_confirm')],
            ['key' => 'ignore', 'label' => __('admin.reconciliation.bulk.ignore'), 'url' => route('admin.api.tables.reconciliation.bulk.ignore'), 'danger' => true, 'confirm' => __('admin.reconciliation.bulk.ignore_confirm')],
        ];
    }

    protected function query(): Builder
    {
        return ReconciliationItem::query()->select('reconciliation_items.*')->with('reconciliationRun:id,kind,subject_key,window_start,window_end');
    }

    /**
     * @param  ReconciliationItem  $row
     */
    protected function cells(Model $row): array
    {
        $run = $row->reconciliationRun;

        return [
            'found' => $this->dateTime($row->created_at),
            'run' => $run === null ? Cell::muted('—') : Cell::stack((string) $run->subject_key, $this->dateTime($run->window_start).' → '.$this->dateTime($run->window_end)),
            'type' => Cell::status('reconciliation_type', $row->type?->value),
            'reference' => Cell::mono($row->provider_reference ?? ($row->reference_type === null ? null : $row->reference_type.' #'.$row->reference_id)),
            'status' => Cell::status('reconciliation_status', $row->status?->value),
            'note' => (string) ($row->resolution_note ?? '—'),
        ];
    }
}
