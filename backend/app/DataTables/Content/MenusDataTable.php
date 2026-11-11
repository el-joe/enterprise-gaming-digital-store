<?php

namespace App\DataTables\Content;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Cms\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Menus (spec Phase 9.9).
 */
class MenusDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'menus';
    }

    public static function permission(): string
    {
        return 'content.menus';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'menus.name', html: true),
            new Column('location', __('admin.col.location'), 'menus.location', html: true),
            new Column('items', __('admin.col.items'), 'items_count', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['menus.name', 'menus.location'];
    }

    protected function query(): Builder
    {
        return Menu::query()->select('menus.*')->withCount('menuItems as items_count');
    }

    /**
     * @param  Menu  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.content.menus.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'location' => Cell::mono($row->location),
            'items' => (string) $row->getAttribute('items_count'),
        ];
    }
}
