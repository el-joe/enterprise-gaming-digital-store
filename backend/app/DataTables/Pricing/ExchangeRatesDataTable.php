<?php

namespace App\DataTables\Pricing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Currency\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Current exchange rates (spec Phase 9.7): provider rate, effective rate with markup or manual override, a spike-held pending rate, and the actions on them for `currency.manage`.
 */
class ExchangeRatesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'exchange-rates';
    }

    public static function permission(): string
    {
        return 'pricing.view|currency.manage';
    }

    public function columns(): array
    {
        return [
            new Column('currency', __('admin.col.currency'), 'exchange_rates.quote_currency', html: true),
            new Column('provider', __('admin.col.provider'), 'exchange_rates.provider_rate', html: true, numeric: true),
            new Column('effective', __('admin.col.effective'), 'exchange_rates.effective_rate', html: true, numeric: true),
            new Column('manual', __('admin.col.manual'), html: true),
            new Column('pending', __('admin.col.pending'), html: true),
            new Column('source', __('admin.col.source'), 'exchange_rates.source'),
            new Column('fetched', __('admin.col.fetched'), 'exchange_rates.fetched_at'),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['currency', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['exchange_rates.quote_currency'];
    }

    protected function query(): Builder
    {
        return ExchangeRate::query()->select('exchange_rates.*');
    }

    /**
     * @param  ExchangeRate  $row
     */
    protected function cells(Model $row): array
    {
        $manage = $this->viewer->can('currency.manage');
        $api = url('/admin/api/exchange-rates/'.$row->quote_currency);
        $reason = __('admin.common.reason');

        return [
            'currency' => Cell::mono($row->base_currency.' → '.$row->quote_currency),
            'provider' => Cell::mono($row->provider_rate === null ? null : (string) $row->provider_rate),
            'effective' => Cell::strong((string) $row->effective_rate),
            'manual' => $row->is_manual_active
                ? Cell::badge(__('admin.rates.manual_until', ['rate' => (string) $row->manual_rate, 'date' => $this->dateTime($row->manual_expires_at)]), 'warning', false)
                : Cell::muted('—'),
            'pending' => $row->pending_rate === null ? Cell::muted('—') : Cell::badge(__('admin.rates.held', ['rate' => (string) $row->pending_rate]), 'danger', false),
            'source' => (string) $row->source,
            'fetched' => $this->dateTime($row->fetched_at),
            'actions' => ! $manage ? '' : Cell::actions(...array_filter([
                $row->pending_rate !== null ? Cell::action(__('admin.rates.approve'), $api.'/approve-pending', confirm: __('admin.rates.approve_confirm'), reason: $reason) : null,
                $row->pending_rate !== null ? Cell::action(__('admin.rates.reject'), $api.'/reject-pending', confirm: __('admin.rates.reject_confirm'), danger: true, reason: $reason) : null,
                $row->is_manual_active ? Cell::action(__('admin.rates.clear'), $api.'/override', 'DELETE', confirm: __('admin.rates.clear_confirm'), danger: true, reason: $reason) : null,
            ])),
        ];
    }
}
