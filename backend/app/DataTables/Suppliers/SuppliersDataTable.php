<?php

namespace App\DataTables\Suppliers;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Suppliers\Enums\SupplierHealth;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Supplier accounts (spec Phase 9.3). Credentials appear only as configured or not configured
 * for the supplier's current mode; the balance is the cached value (no live call).
 */
class SuppliersDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'suppliers';
    }

    public static function permission(): string
    {
        return 'suppliers.view';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.suppliers.col.name'), 'suppliers.name', html: true),
            new Column('adapter', __('admin.suppliers.col.adapter'), 'suppliers.adapter_key', html: true),
            new Column('status', __('admin.suppliers.col.status'), 'suppliers.status', html: true),
            new Column('mode', __('admin.suppliers.col.mode'), 'suppliers.mode', html: true),
            new Column('health', __('admin.suppliers.col.health'), 'suppliers.health', html: true),
            new Column('balance', __('admin.suppliers.col.balance'), 'suppliers.last_balance', html: true, numeric: true),
            new Column('mappings', __('admin.suppliers.col.mappings'), 'mappings_count', numeric: true),
            new Column('credentials', __('admin.suppliers.col.credentials'), html: true),
            new Column('priority', __('admin.suppliers.col.priority'), 'suppliers.priority', numeric: true),
            new Column('actions', __('admin.col.actions'), html: true, exportable: false),
        ];
    }

    public function defaultOrder(): array
    {
        return ['priority', 'asc'];
    }

    public function filters(): array
    {
        return [
            new Filter('status', __('admin.suppliers.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('suppliers.status', $value);
            }, $this->options('supplier_status', SupplierStatus::cases())),
            new Filter('health', __('admin.suppliers.col.health'), 'select', static function (Builder $query, string $value): void {
                $query->where('suppliers.health', $value);
            }, $this->options('supplier_health', SupplierHealth::cases())),
        ];
    }

    protected function searchColumns(): array
    {
        return ['suppliers.name', 'suppliers.adapter_key'];
    }

    protected function query(): Builder
    {
        return Supplier::query()
            ->select('suppliers.*')
            ->withCount('supplierProductMappings as mappings_count')
            ->withExists(['supplierCredentials as credentials_configured' => static function (Builder $query): void {
                $query->whereColumn('supplier_credentials.mode', 'suppliers.mode');
            }]);
    }

    /**
     * @param  Supplier  $row
     */
    protected function cells(Model $row): array
    {
        $open = $row->circuit_open_until !== null && $row->circuit_open_until->isFuture();

        return [
            'name' => Cell::link(route('admin.suppliers.show', $row), (string) $row->name),
            'adapter' => Cell::mono($row->adapter_key),
            'status' => Cell::status('supplier_status', $row->status?->value),
            'mode' => Cell::status('mode', $row->mode?->value),
            'health' => $open ? Cell::badge(__('admin.suppliers.circuit_open'), 'danger') : Cell::status('supplier_health', $row->health?->value),
            'balance' => Cell::strong($this->money($row->last_balance, $row->balance_currency)),
            'mappings' => (string) $row->getAttribute('mappings_count'),
            'credentials' => $row->getAttribute('credentials_configured')
                ? Cell::badge(__('admin.common.configured'), 'success', false)
                : Cell::badge(__('admin.common.not_configured'), 'neutral', false),
            'priority' => (string) $row->priority,
            'actions' => $this->viewer->can('suppliers.manage')
                ? Cell::actions('<a href="'.e(route('admin.suppliers.show', $row).'#supplier-edit').'" class="admin-btn h-8 min-h-8">'.e(__('admin.common.edit')).'</a>')
                : Cell::actions('<a href="'.e(route('admin.suppliers.show', $row)).'" class="admin-btn h-8 min-h-8">'.e(__('admin.suppliers.open')).'</a>'),
        ];
    }

    /**
     * @param  list<\BackedEnum>  $cases
     * @return array<string, string>
     */
    private function options(string $group, array $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $options[(string) $case->value] = Cell::label($group, (string) $case->value);
        }

        return $options;
    }
}
