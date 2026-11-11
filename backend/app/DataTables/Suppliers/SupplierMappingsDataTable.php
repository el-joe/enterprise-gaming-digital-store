<?php

namespace App\DataTables\Suppliers;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A supplier's product mappings (spec Phase 9.3 "Product mapping"): which variant is bought from
 * which supplier product, at what supplier price.
 */
class SupplierMappingsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'supplier-mappings';
    }

    public static function permission(): string
    {
        return 'suppliers.view|mappings.manage';
    }

    public function columns(): array
    {
        return [
            new Column('variant', __('admin.mappings.col.variant'), html: true),
            new Column('external', __('admin.mappings.col.external'), html: true),
            new Column('role', __('admin.mappings.col.role'), 'supplier_product_mappings.role'),
            new Column('priority', __('admin.mappings.col.priority'), 'supplier_product_mappings.priority', numeric: true),
            new Column('price', __('admin.mappings.col.price'), 'supplier_product_mappings.supplier_unit_price', numeric: true),
            new Column('availability', __('admin.mappings.col.availability'), 'supplier_product_mappings.availability'),
            new Column('active', __('admin.mappings.col.active'), 'supplier_product_mappings.is_active', html: true),
            new Column('verified', __('admin.mappings.col.verified'), 'supplier_product_mappings.last_verified_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['priority', 'asc'];
    }

    public function filters(): array
    {
        return [
            new Filter('supplier', '', 'context', static function (Builder $query, string $value): void {
                $query->where('supplier_product_mappings.supplier_id', (int) $value);
            }),
            new Filter('active', __('admin.mappings.col.active'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_product_mappings.is_active', $value === '1');
            }, ['1' => __('admin.common.yes'), '0' => __('admin.common.no')]),
        ];
    }

    protected function query(): Builder
    {
        return SupplierProductMapping::query()
            ->select('supplier_product_mappings.*')
            ->with(['productVariant:id,product_id,sku,title', 'productVariant.product:id,name', 'supplierProduct:id,external_id,name']);
    }

    /**
     * @param  SupplierProductMapping  $row
     */
    protected function cells(Model $row): array
    {
        $variant = $row->productVariant;

        return [
            'variant' => Cell::stack((string) ($variant?->product?->name ?? '—'), trim(((string) $variant?->title).' · '.((string) $variant?->sku), ' ·')),
            'external' => Cell::stack((string) ($row->supplierProduct?->name ?? '—'), $row->supplierProduct?->external_id),
            'role' => Cell::label('mapping_role', $row->role?->value),
            'priority' => (string) $row->priority,
            'price' => $this->money($row->supplier_unit_price, $row->supplier_unit_currency),
            'availability' => Cell::label('availability', $row->availability?->value),
            'active' => Cell::yesNo((bool) $row->is_active),
            'verified' => $this->dateTime($row->last_verified_at),
        ];
    }
}
