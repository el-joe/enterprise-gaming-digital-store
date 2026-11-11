<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Http\Support\AdminScreens\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Every supplier mapping (spec Phase 5 "Multi-Supplier Product Mapping", 9.3), with bulk activate and deactivate.
 */
class MappingsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'mappings';
    }

    public static function permission(): string
    {
        return 'mappings.manage';
    }

    public function columns(): array
    {
        return [
            new Column('variant', __('admin.col.variant'), html: true),
            new Column('supplier', __('admin.col.supplier'), html: true),
            new Column('external', __('admin.col.external'), html: true),
            new Column('role', __('admin.col.role'), 'supplier_product_mappings.role'),
            new Column('priority', __('admin.col.priority'), 'supplier_product_mappings.priority', numeric: true),
            new Column('price', __('admin.col.price'), 'supplier_product_mappings.supplier_unit_price', numeric: true),
            new Column('active', __('admin.col.active'), 'supplier_product_mappings.is_active', html: true),
            new Column('verified', __('admin.col.verified'), 'supplier_product_mappings.last_verified_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['priority', 'asc'];
    }

    public function filters(): array
    {
        return [
            new Filter('supplier', __('admin.col.supplier'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_product_mappings.supplier_id', (int) $value);
            }, array_map('strval', Options::suppliers())),
            $this->booleanFilter('active', __('admin.col.active'), 'supplier_product_mappings.is_active'),
        ];
    }

    public function bulkActions(): array
    {
        $url = route('admin.api.supplier-mappings.bulk-status');

        return [
            ['key' => 'activate', 'label' => __('admin.mappings_bulk.activate'), 'url' => $url, 'danger' => false, 'confirm' => __('admin.mappings_bulk.activate_confirm'), 'body' => ['is_active' => true]],
            ['key' => 'deactivate', 'label' => __('admin.mappings_bulk.deactivate'), 'url' => $url, 'danger' => true, 'confirm' => __('admin.mappings_bulk.deactivate_confirm'), 'body' => ['is_active' => false]],
        ];
    }

    protected function query(): Builder
    {
        return SupplierProductMapping::query()->select('supplier_product_mappings.*')->with(['productVariant:id,product_id,sku,title', 'productVariant.product:id,public_id,name', 'supplier:id,name', 'supplierProduct:id,external_id,name']);
    }

    /**
     * @param  SupplierProductMapping  $row
     */
    protected function cells(Model $row): array
    {
        $variant = $row->productVariant;

        return [
            'variant' => $variant?->product === null ? Cell::muted('—') : Cell::link(route('admin.catalog.products.edit', ['product' => $variant->product->public_id]), (string) $variant->product->name).Cell::muted(' '.trim(((string) $variant->title).' · '.((string) $variant->sku), ' ·')),
            'supplier' => e((string) ($row->supplier?->name ?? '—')),
            'external' => Cell::stack((string) ($row->supplierProduct?->name ?? '—'), $row->supplierProduct?->external_id),
            'role' => Cell::label('mapping_role', $row->role?->value),
            'priority' => (string) $row->priority,
            'price' => $this->money($row->supplier_unit_price, $row->supplier_unit_currency),
            'active' => Cell::yesNo((bool) $row->is_active),
            'verified' => $this->dateTime($row->last_verified_at),
        ];
    }
}
