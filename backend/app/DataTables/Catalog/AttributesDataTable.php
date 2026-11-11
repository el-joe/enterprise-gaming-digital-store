<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Catalog\Models\AttributeDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Product attribute definitions (spec Phase 9.2).
 */
class AttributesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'attributes';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'attribute_definitions.name', html: true),
            new Column('code', __('admin.col.code'), 'attribute_definitions.code', html: true),
            new Column('type', __('admin.col.type'), 'attribute_definitions.type'),
            new Column('filterable', __('admin.col.filterable'), 'attribute_definitions.is_filterable', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['attribute_definitions.name', 'attribute_definitions.code'];
    }

    protected function query(): Builder
    {
        return AttributeDefinition::query()->select('attribute_definitions.*');
    }

    /**
     * @param  AttributeDefinition  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.catalog.attributes.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'code' => Cell::mono($row->code),
            'type' => Cell::label('attribute_type', $row->type?->value),
            'filterable' => Cell::yesNo((bool) $row->is_filterable),
        ];
    }
}
