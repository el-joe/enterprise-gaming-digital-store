<?php

namespace App\DataTables\Marketing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Promotions\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Coupons (spec Phase 9.8).
 */
class CouponsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'coupons';
    }

    public static function permission(): string
    {
        return 'marketing.coupons';
    }

    public function columns(): array
    {
        return [
            new Column('code', __('admin.col.code'), 'coupons.code', html: true),
            new Column('type', __('admin.col.type'), 'coupons.type'),
            new Column('value', __('admin.col.value'), 'coupons.value', numeric: true),
            new Column('used', __('admin.col.used'), 'coupons.used_count', html: true, numeric: true),
            new Column('window', __('admin.col.window'), html: true),
            new Column('created_by', __('admin.col.created_by'), 'coupons.created_by_type'),
            new Column('active', __('admin.col.active'), 'coupons.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['code', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['coupons.code'];
    }

    public function filters(): array
    {
        return [$this->booleanFilter('active', __('admin.col.active'), 'coupons.is_active')];
    }

    protected function query(): Builder
    {
        return Coupon::query()->select('coupons.*');
    }

    /**
     * @param  Coupon  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.marketing.coupons.edit', ['record' => $row->getRouteKey()]);

        return [
            'code' => Cell::link($edit, (string) $row->code, true),
            'type' => Cell::label('coupon_type', $row->type?->value),
            'value' => $row->type?->value === 'percent' ? $row->value.'%' : $this->money($row->value, $row->currency ?? 'USD'),
            'used' => e((string) $row->used_count).Cell::muted($row->usage_limit === null ? ' / ∞' : ' / '.$row->usage_limit),
            'window' => Cell::muted($this->dateTime($row->starts_at).' → '.$this->dateTime($row->ends_at)),
            'created_by' => Cell::label('actor', $row->created_by_type?->value),
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
