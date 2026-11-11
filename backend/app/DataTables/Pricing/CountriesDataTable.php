<?php

namespace App\DataTables\Pricing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Shared\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Countries and their display currency (OD-1).
 */
class CountriesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'countries';
    }

    public static function permission(): string
    {
        return 'pricing.view|currency.manage';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'countries.name', html: true),
            new Column('code', __('admin.col.code'), 'countries.code', html: true),
            new Column('currency', __('admin.col.currency'), 'countries.default_currency', html: true),
            new Column('active', __('admin.col.active'), 'countries.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['countries.name', 'countries.code'];
    }

    protected function idColumn(): string
    {
        return 'countries.code';
    }

    protected function query(): Builder
    {
        return Country::query()->select('countries.*');
    }

    /**
     * @param  Country  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.pricing.countries.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'code' => Cell::mono($row->code),
            'currency' => Cell::mono($row->default_currency),
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
