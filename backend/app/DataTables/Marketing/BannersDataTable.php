<?php

namespace App\DataTables\Marketing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\Banner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Banners (spec Phase 9.8 "Homepage banners").
 */
class BannersDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'banners';
    }

    public static function permission(): string
    {
        return 'marketing.banners';
    }

    public function columns(): array
    {
        return [
            new Column('title', __('admin.col.title'), 'banners.title', html: true),
            new Column('placement', __('admin.col.placement'), 'banners.placement'),
            new Column('window', __('admin.col.window'), html: true),
            new Column('sort_order', __('admin.col.sort_order'), 'banners.sort_order', numeric: true),
            new Column('active', __('admin.col.active'), 'banners.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['banners.title'];
    }

    protected function query(): Builder
    {
        return Banner::query()->select('banners.*');
    }

    /**
     * @param  Banner  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.marketing.banners.edit', ['record' => $row->getRouteKey()]);

        return [
            'title' => Cell::link($edit, (string) ($row->title ?? '#'.$row->id)),
            'placement' => Cell::label('banner_placement', $row->placement?->value),
            'window' => Cell::muted($this->dateTime($row->starts_at).' → '.$this->dateTime($row->ends_at)),
            'sort_order' => (string) $row->sort_order,
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
