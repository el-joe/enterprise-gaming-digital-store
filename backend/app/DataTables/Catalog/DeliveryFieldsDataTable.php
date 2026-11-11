<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Delivery field definitions (spec Phase 9.2 "Required delivery fields").
 */
class DeliveryFieldsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'delivery-fields';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        return [
            new Column('label', __('admin.col.label'), 'delivery_field_definitions.label', html: true),
            new Column('code', __('admin.col.code'), 'delivery_field_definitions.code', html: true),
            new Column('input_type', __('admin.col.input_type'), 'delivery_field_definitions.input_type'),
            new Column('validation', __('admin.col.validation'), html: true),
            new Column('products', __('admin.col.products'), 'products_count', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['label', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['delivery_field_definitions.label', 'delivery_field_definitions.code'];
    }

    protected function query(): Builder
    {
        return DeliveryFieldDefinition::query()->select('delivery_field_definitions.*')->withCount('productDeliveryFields as products_count');
    }

    /**
     * @param  DeliveryFieldDefinition  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.catalog.delivery-fields.edit', ['record' => $row->getRouteKey()]);

        return [
            'label' => Cell::link($edit, (string) $row->label),
            'code' => Cell::mono($row->code),
            'input_type' => Cell::label('input_type', $row->input_type?->value),
            'validation' => Cell::mono($row->validation_regex),
            'products' => (string) $row->getAttribute('products_count'),
        ];
    }
}
