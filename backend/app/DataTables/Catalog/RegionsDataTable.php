<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Catalog\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Regions (spec Phase 9.2).
 */
class RegionsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'regions';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'regions.name', html: true),
            new Column('code', __('admin.col.code'), 'regions.code', html: true),
            new Column('global', __('admin.col.global'), 'regions.is_global', html: true),
            new Column('countries', __('admin.col.countries'), 'countries_count', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['regions.name', 'regions.code'];
    }

    protected function query(): Builder
    {
        return Region::query()->select('regions.*')->withCount('countries');
    }

    /**
     * @param  Region  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.catalog.regions.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'code' => Cell::mono($row->code),
            'global' => Cell::yesNo((bool) $row->is_global),
            'countries' => (string) $row->getAttribute('countries_count'),
        ];
    }
}
