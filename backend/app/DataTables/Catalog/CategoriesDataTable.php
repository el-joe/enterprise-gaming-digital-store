<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Categories (spec Phase 9.2).
 */
class CategoriesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'categories';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        return [
            new Column('image', __('admin.col.image'), html: true, exportable: false),
            new Column('name', __('admin.col.name'), 'categories.name', html: true),
            new Column('slug', __('admin.col.slug'), 'categories.slug', html: true),
            new Column('parent', __('admin.col.parent'), html: true),
            new Column('active', __('admin.col.active'), 'categories.is_active', html: true),
            new Column('sort_order', __('admin.col.sort_order'), 'categories.sort_order', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    public function filters(): array
    {
        return [$this->booleanFilter('active', __('admin.col.active'), 'categories.is_active')];
    }

    protected function searchColumns(): array
    {
        return ['categories.name', 'categories.slug'];
    }

    protected function query(): Builder
    {
        return Category::query()->select('categories.*')->with(['parent:id,name', 'bannerMedia']);
    }

    /**
     * @param  Category  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.catalog.categories.edit', ['record' => $row->getRouteKey()]);

        return [
            'image' => Cell::thumbnail($row->bannerMedia, (string) $row->name),
            'name' => Cell::link($edit, (string) $row->name),
            'slug' => Cell::mono($row->slug),
            'parent' => $row->parent === null ? Cell::muted('—') : e((string) $row->parent->name),
            'active' => Cell::yesNo((bool) $row->is_active),
            'sort_order' => (string) $row->sort_order,
        ];
    }
}
