<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Catalog\Models\GamingPlatform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Gaming platforms (spec Phase 9.2).
 */
class PlatformsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'platforms';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'gaming_platforms.name', html: true),
            new Column('slug', __('admin.col.slug'), 'gaming_platforms.slug', html: true),
            new Column('products', __('admin.col.products'), 'products_count', numeric: true),
            new Column('featured', __('admin.col.featured'), 'gaming_platforms.is_featured', html: true),
            new Column('active', __('admin.col.active'), 'gaming_platforms.is_active', html: true),
            new Column('sort_order', __('admin.col.sort_order'), 'gaming_platforms.sort_order', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    public function filters(): array
    {
        return [$this->booleanFilter('active', __('admin.col.active'), 'gaming_platforms.is_active')];
    }

    protected function searchColumns(): array
    {
        return ['gaming_platforms.name', 'gaming_platforms.slug'];
    }

    protected function query(): Builder
    {
        return GamingPlatform::query()->select('gaming_platforms.*')->withCount('products');
    }

    /**
     * @param  GamingPlatform  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.catalog.platforms.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'slug' => Cell::mono($row->slug),
            'products' => (string) $row->getAttribute('products_count'),
            'featured' => Cell::yesNo((bool) $row->is_featured),
            'active' => Cell::yesNo((bool) $row->is_active),
            'sort_order' => (string) $row->sort_order,
        ];
    }
}
