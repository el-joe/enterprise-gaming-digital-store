<?php

namespace App\DataTables\Marketing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Affiliates\Models\Affiliate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Affiliates (spec Phase 9.8). Payout details are never listed.
 */
class AffiliatesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'affiliates';
    }

    public static function permission(): string
    {
        return 'marketing.affiliates';
    }

    public function columns(): array
    {
        return [
            new Column('code', __('admin.col.code'), 'affiliates.code', html: true),
            new Column('customer', __('admin.col.customer'), html: true),
            new Column('status', __('admin.col.status'), 'affiliates.status', html: true),
            new Column('commission', __('admin.col.commission'), 'affiliates.commission_pct', numeric: true),
            new Column('referrals', __('admin.col.referrals'), 'referrals_count', numeric: true),
            new Column('created', __('admin.col.created'), 'affiliates.created_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['affiliates.code'];
    }

    public function filters(): array
    {
        return [new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
            $query->where('affiliates.status', $value);
        }, $this->enumOptions('affiliate_status', AffiliateStatus::cases()))];
    }

    protected function query(): Builder
    {
        return Affiliate::query()->select('affiliates.*')->with('user:id,name')->withCount('affiliateReferrals as referrals_count');
    }

    /**
     * @param  Affiliate  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.marketing.affiliates.edit', ['record' => $row->getRouteKey()]);

        return [
            'code' => Cell::link($edit, (string) $row->code, true),
            'customer' => e((string) ($row->user?->name ?? '—')),
            'status' => Cell::status('affiliate_status', $row->status?->value),
            'commission' => $row->commission_pct.'%',
            'referrals' => (string) $row->getAttribute('referrals_count'),
            'created' => $this->dateTime($row->created_at),
        ];
    }
}
