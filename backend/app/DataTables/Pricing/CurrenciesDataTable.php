<?php

namespace App\DataTables\Pricing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Currencies (spec Phase 9.7): display currencies are the active ones.
 */
class CurrenciesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'currencies';
    }

    public static function permission(): string
    {
        return 'pricing.view|currency.manage';
    }

    public function columns(): array
    {
        return [
            new Column('code', __('admin.col.code'), 'currencies.code', html: true),
            new Column('name', __('admin.col.name'), 'currencies.name'),
            new Column('symbol', __('admin.col.symbol')),
            new Column('markup', __('admin.col.markup'), 'currencies.markup_pct', numeric: true),
            new Column('rate', __('admin.col.rate'), html: true),
            new Column('active', __('admin.col.active'), 'currencies.is_active', html: true),
            new Column('sort_order', __('admin.col.sort_order'), 'currencies.sort_order', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['sort_order', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['currencies.code', 'currencies.name'];
    }

    public function filters(): array
    {
        return [$this->booleanFilter('active', __('admin.col.active'), 'currencies.is_active')];
    }

    protected function query(): Builder
    {
        return Currency::query()->select('currencies.*')->addSelect(['rate_value' => ExchangeRate::query()->select('effective_rate')->whereColumn('exchange_rates.quote_currency', 'currencies.code')->limit(1)]);
    }

    /**
     * @param  Currency  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.pricing.currencies.edit', ['record' => $row->getRouteKey()]);

        return [
            'code' => Cell::link($edit, (string) $row->code),
            'name' => (string) $row->name,
            'symbol' => (string) $row->symbol,
            'markup' => $row->markup_pct.'%',
            'rate' => Cell::mono($row->getAttribute('rate_value') === null ? null : (string) $row->getAttribute('rate_value')),
            'active' => Cell::yesNo((bool) $row->is_active),
            'sort_order' => (string) $row->sort_order,
        ];
    }
}
