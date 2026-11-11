<?php

namespace App\DataTables\Marketing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Promotions\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Promotions and discount campaigns (spec Phase 9.8).
 */
class PromotionsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'promotions';
    }

    public static function permission(): string
    {
        return 'marketing.promotions';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'promotions.name', html: true),
            new Column('campaign', __('admin.col.campaign'), 'promotions.campaign'),
            new Column('type', __('admin.col.type'), 'promotions.type'),
            new Column('value', __('admin.col.value'), 'promotions.value', numeric: true),
            new Column('targets', __('admin.col.targets'), 'targets_count', numeric: true),
            new Column('window', __('admin.col.window'), html: true),
            new Column('active', __('admin.col.active'), 'promotions.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['promotions.name', 'promotions.campaign'];
    }

    protected function query(): Builder
    {
        return Promotion::query()->select('promotions.*')->withCount('promotionTargets as targets_count');
    }

    /**
     * @param  Promotion  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.marketing.promotions.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'campaign' => (string) ($row->campaign ?? '—'),
            'type' => Cell::label('promotion_type', $row->type?->value),
            'value' => $row->type?->value === 'percent' ? $row->value.'%' : (string) $row->value,
            'targets' => (string) $row->getAttribute('targets_count'),
            'window' => Cell::muted($this->dateTime($row->starts_at).' → '.$this->dateTime($row->ends_at)),
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
