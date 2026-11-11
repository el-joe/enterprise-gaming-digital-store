<?php

namespace App\DataTables\Marketing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Affiliates\Enums\AffiliateCommissionStatus;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Commissions of one affiliate (spec Phase 9.8 "Affiliate management"), with approval of pending ones.
 */
class AffiliateCommissionsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'affiliate-commissions';
    }

    public static function permission(): string
    {
        return 'marketing.affiliates';
    }

    public function columns(): array
    {
        return [
            new Column('order', __('admin.col.order'), html: true),
            new Column('amount', __('admin.col.amount'), 'affiliate_commissions.amount_base', html: true, numeric: true),
            new Column('status', __('admin.col.status'), 'affiliate_commissions.status', html: true),
            new Column('created', __('admin.col.created'), 'affiliate_commissions.created_at'),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    public function filters(): array
    {
        return [
            new Filter('affiliate', '', 'context', static function (Builder $query, string $value): void {
                $query->where('affiliate_commissions.affiliate_id', (int) $value);
            }),
            new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('affiliate_commissions.status', $value);
            }, $this->enumOptions('commission_status', AffiliateCommissionStatus::cases())),
        ];
    }

    protected function query(): Builder
    {
        return AffiliateCommission::query()->select('affiliate_commissions.*')->with('order:id,public_id,order_number');
    }

    /**
     * @param  AffiliateCommission  $row
     */
    protected function cells(Model $row): array
    {
        $base = app(ExchangeRateRegistry::class)->baseCurrency();

        return [
            'order' => $row->order === null ? Cell::muted('—') : Cell::link(route('admin.orders.show', $row->order->public_id), (string) ($row->order->order_number ?? $row->order->public_id), true),
            'amount' => Cell::strong($this->money($row->amount_base, $base)),
            'status' => Cell::status('commission_status', $row->status?->value),
            'created' => $this->dateTime($row->created_at),
            'actions' => $row->status === AffiliateCommissionStatus::Pending
                ? Cell::action(__('admin.affiliates.approve'), route('admin.api.affiliate-commissions.approve', $row->id), confirm: __('admin.affiliates.approve_confirm'))
                : '',
        ];
    }
}
