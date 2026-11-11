<?php

namespace App\DataTables\Pricing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Pricing\Models\VariantPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Country and currency price overrides (spec Phase 8).
 */
class VariantPricesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'variant-prices';
    }

    public static function permission(): string
    {
        return 'pricing.view';
    }

    public function columns(): array
    {
        return [
            new Column('variant', __('admin.col.variant'), html: true),
            new Column('country', __('admin.col.country'), 'variant_prices.country_code', html: true),
            new Column('price', __('admin.col.price'), 'variant_prices.price', html: true, numeric: true),
            new Column('compare_at', __('admin.col.compare_at'), 'variant_prices.compare_at', numeric: true),
            new Column('window', __('admin.col.window'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['country', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['variant_prices.country_code', 'variant_prices.currency'];
    }

    protected function query(): Builder
    {
        return VariantPrice::query()->select('variant_prices.*')->with(['productVariant:id,product_id,sku,title', 'productVariant.product:id,name']);
    }

    /**
     * @param  VariantPrice  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.pricing.variant-prices.edit', ['record' => $row->getRouteKey()]);

        return [
            'variant' => Cell::link($edit, (string) ($row->productVariant?->product?->name ?? '—')).'<span class="block text-xs text-content-muted">'.e(trim(((string) $row->productVariant?->title).' · '.((string) $row->productVariant?->sku), ' ·')).'</span>',
            'country' => Cell::mono($row->country_code),
            'price' => Cell::strong($this->money($row->price, $row->currency)),
            'compare_at' => $this->money($row->compare_at, $row->currency),
            'window' => Cell::muted($this->dateTime($row->starts_at).' → '.$this->dateTime($row->ends_at)),
        ];
    }
}
