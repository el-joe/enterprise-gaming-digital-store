<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Http\Support\AdminScreens\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * The synced supplier catalog (`supplier_products`), with "Add to store" per row and in bulk for
 * staff who hold catalog.manage and mappings.manage. "In store" means the supplier product has a
 * mapping; it links to the store product.
 */
class SupplierCatalogDataTable extends AdminDataTable
{
    public const string FIELDS_TEMPLATE = 'supplier-catalog-add-fields';

    public static function key(): string
    {
        return 'supplier-catalog';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        $columns = [
            new Column('image', __('admin.supplier_catalog.col_image'), html: true, exportable: false),
            new Column('supplier', __('admin.col.supplier'), 'supplier_products.supplier_id'),
            new Column('external', __('admin.supplier_catalog.col_external'), 'supplier_products.external_id', html: true),
            new Column('name', __('admin.supplier_catalog.col_name'), 'supplier_products.name'),
            new Column('brand', __('admin.supplier_catalog.col_brand'), 'supplier_products.brand_name'),
            new Column('category', __('admin.supplier_catalog.col_category'), 'supplier_products.category_name'),
            new Column('country', __('admin.supplier_catalog.col_country'), 'supplier_products.country_code'),
            new Column('currency', __('admin.supplier_catalog.col_currency'), 'supplier_products.currency'),
            new Column('type', __('admin.supplier_catalog.col_type'), 'supplier_products.denomination_type'),
            new Column('amounts', __('admin.supplier_catalog.col_amounts'), html: true),
            new Column('cost', __('admin.supplier_catalog.col_cost'), numeric: true),
            new Column('status', __('admin.supplier_catalog.col_status'), 'supplier_products.availability', html: true),
            new Column('in_store', __('admin.supplier_catalog.col_in_store'), html: true),
            new Column('synced', __('admin.supplier_catalog.col_synced'), 'supplier_products.last_seen_at'),
        ];

        if ($this->canAdd()) {
            $columns[] = new Column('actions', __('admin.common.actions'), html: true, exportable: false);
        }

        return $columns;
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['supplier_products.name', 'supplier_products.brand_name', 'supplier_products.external_id'];
    }

    public function filters(): array
    {
        return [
            new Filter('supplier', __('admin.col.supplier'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_products.supplier_id', (int) $value);
            }, array_map('strval', Options::suppliers())),
            new Filter('brand', __('admin.supplier_catalog.col_brand'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_products.brand_name', $value);
            }, $this->distinct('brand_name')),
            new Filter('category', __('admin.supplier_catalog.col_category'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_products.category_name', $value);
            }, $this->distinct('category_name')),
            new Filter('country', __('admin.supplier_catalog.col_country'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_products.country_code', $value);
            }, $this->distinct('country_code')),
            new Filter('currency', __('admin.supplier_catalog.col_currency'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_products.currency', $value);
            }, $this->distinct('currency')),
            new Filter('type', __('admin.supplier_catalog.col_type'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_products.denomination_type', $value);
            }, $this->enumOptions('denomination_type', DenominationType::cases())),
            new Filter('in_store', __('admin.supplier_catalog.col_in_store'), 'select', static function (Builder $query, string $value): void {
                $value === '1' ? $query->whereHas('supplierProductMappings') : $query->whereDoesntHave('supplierProductMappings');
            }, ['1' => (string) __('admin.common.yes'), '0' => (string) __('admin.common.no')]),
            new Filter('status', __('admin.supplier_catalog.col_status'), 'select', static function (Builder $query, string $value): void {
                $value === 'removed'
                    ? $query->whereNotNull('supplier_products.removed_at')
                    : $query->whereNull('supplier_products.removed_at')->where('supplier_products.availability', $value);
            }, [...$this->enumOptions('availability', SupplierAvailability::cases()), 'removed' => (string) __('admin.supplier_catalog.removed')]),
        ];
    }

    public function bulkActions(): array
    {
        if (! $this->canAdd()) {
            return [];
        }

        return [
            ['key' => 'add_to_store', 'label' => __('admin.supplier_catalog.add'), 'url' => route('admin.api.supplier-catalog.add-to-store'), 'danger' => false, 'confirm' => __('admin.supplier_catalog.add_confirm'), 'fields' => self::FIELDS_TEMPLATE],
        ];
    }

    protected function query(): Builder
    {
        return SupplierProduct::query()->select('supplier_products.*')
            ->selectSub(self::latestPrice('cost'), 'latest_cost')
            ->selectSub(self::latestPrice('cost_currency'), 'latest_cost_currency')
            ->with([
                'supplier:id,name',
                'imageMedia:id,disk,path,conversions',
                'supplierProductMappings' => static fn ($query) => $query->select(['id', 'supplier_product_id', 'product_variant_id'])->orderBy('id'),
                'supplierProductMappings.productVariant:id,product_id',
                'supplierProductMappings.productVariant.product:id,public_id,name',
            ]);
    }

    /**
     * @param  SupplierProduct  $row
     */
    protected function cells(Model $row): array
    {
        $product = $row->supplierProductMappings->first()?->productVariant?->product;
        $linked = $row->supplierProductMappings->isNotEmpty();

        return [
            'image' => $this->thumbnail($row),
            'supplier' => (string) ($row->supplier?->name ?? '—'),
            'external' => Cell::mono($row->external_id),
            'name' => $row->name,
            'brand' => (string) ($row->brand_name ?? '—'),
            'category' => (string) ($row->category_name ?? '—'),
            'country' => (string) ($row->country_code ?? '—'),
            'currency' => (string) ($row->currency ?? '—'),
            'type' => Cell::label('denomination_type', $row->denomination_type?->value),
            'amounts' => $this->amounts($row),
            'cost' => $this->money($row->getAttribute('latest_cost'), $row->getAttribute('latest_cost_currency')),
            'status' => $row->removed_at !== null
                ? Cell::badge((string) __('admin.supplier_catalog.removed'), 'danger')
                : Cell::status('availability', $row->availability?->value),
            'in_store' => match (true) {
                $product !== null => Cell::link(route('admin.catalog.products.edit', ['product' => $product->public_id]), (string) __('admin.supplier_catalog.in_store_yes')),
                $linked => Cell::badge((string) __('admin.common.yes'), 'success', false),
                default => Cell::badge((string) __('admin.common.no'), 'neutral', false),
            },
            'synced' => $this->dateTime($row->last_seen_at),
            'actions' => $linked || $row->removed_at !== null ? '' : Cell::action(
                (string) __('admin.supplier_catalog.add'),
                route('admin.api.supplier-catalog.add-to-store'),
                body: ['ids' => [$row->id]],
                confirm: (string) __('admin.supplier_catalog.add_confirm'),
                fields: self::FIELDS_TEMPLATE,
            ),
        ];
    }

    /**
     * The synced supplier image (owner decision 12) as a small lazy thumbnail (the 480 px WebP).
     */
    private function thumbnail(SupplierProduct $row): string
    {
        $media = $row->imageMedia;

        if ($media === null) {
            return Cell::muted('—');
        }

        $conversions = (array) $media->conversions;
        $path = $conversions['webp_480'] ?? $conversions['webp'] ?? $media->path;

        return '<img src="'.e(app(MediaUrls::class)->url((string) $media->disk, (string) $path)).'" alt="" width="48" height="32" class="h-8 w-12 rounded-sm bg-surface-sunken object-cover" loading="lazy" decoding="async">';
    }

    private function canAdd(): bool
    {
        return $this->viewer->can('catalog.manage') && $this->viewer->can('mappings.manage');
    }

    private function amounts(SupplierProduct $row): string
    {
        if ($row->denomination_type === DenominationType::Range) {
            return Cell::strong($this->plain((string) $row->min_denomination).' – '.$this->plain((string) $row->max_denomination));
        }

        $values = array_map(fn (mixed $value): string => $this->plain((string) $value), is_array($row->fixed_denominations) ? $row->fixed_denominations : []);

        if ($values === []) {
            return Cell::muted('—');
        }

        $shown = implode(', ', array_slice($values, 0, 6));

        return '<span dir="ltr">'.e(count($values) > 6 ? $shown.' (+'.(count($values) - 6).')' : $shown).'</span>';
    }

    private function plain(string $amount): string
    {
        return str_contains($amount, '.') ? rtrim(rtrim($amount, '0'), '.') : $amount;
    }

    /**
     * Distinct non-empty values of a supplier product column, for a select filter.
     *
     * @return array<string, string>
     */
    private function distinct(string $column): array
    {
        return SupplierProduct::query()->whereNotNull($column)->where($column, '!=', '')
            ->distinct()->orderBy($column)->limit(1000)->pluck($column, $column)->map('strval')->all();
    }

    private static function latestPrice(string $column): QueryBuilder
    {
        return DB::table('supplier_product_prices')->select($column)
            ->whereColumn('supplier_product_prices.supplier_product_id', 'supplier_products.id')
            ->orderByDesc('supplier_product_prices.id')->limit(1);
    }
}
