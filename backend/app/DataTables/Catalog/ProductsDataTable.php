<?php

namespace App\DataTables\Catalog;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Http\Support\AdminScreens\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Products (spec Phase 9.2): status, platform, category, variants, featured; bulk publish, unpublish and delete by permission.
 */
class ProductsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'products';
    }

    public static function permission(): string
    {
        return 'catalog.view';
    }

    public function columns(): array
    {
        return [
            new Column('image', __('admin.col.image'), html: true, exportable: false),
            new Column('name', __('admin.col.name'), 'products.name', html: true),
            new Column('sku', __('admin.col.sku'), 'products.sku', html: true),
            new Column('platform', __('admin.col.platform'), html: true),
            new Column('category', __('admin.col.category'), html: true),
            new Column('status', __('admin.col.status'), 'products.status', html: true),
            new Column('variants', __('admin.col.variants'), 'variants_count', numeric: true),
            new Column('featured', __('admin.col.featured'), 'products.is_featured', html: true),
            new Column('updated', __('admin.col.updated'), 'products.updated_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['updated', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['products.name', 'products.sku', 'products.slug'];
    }

    public function filters(): array
    {
        return [
            new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('products.status', $value);
            }, $this->enumOptions('product_status', ProductStatus::cases())),
            new Filter('platform', __('admin.col.platform'), 'select', static function (Builder $query, string $value): void {
                $query->where('products.gaming_platform_id', (int) $value);
            }, array_map('strval', Options::platforms())),
            $this->booleanFilter('featured', __('admin.col.featured'), 'products.is_featured'),
        ];
    }

    public function rowId(Model $row): string
    {
        return (string) $row->getAttribute('public_id');
    }

    protected function idColumn(): string
    {
        return 'products.public_id';
    }

    public function bulkActions(): array
    {
        $actions = [];
        $url = route('admin.api.products.bulk');

        if ($this->viewer->can('catalog.publish')) {
            $actions[] = ['key' => 'activate', 'label' => __('admin.products.bulk_activate'), 'url' => $url, 'danger' => false, 'confirm' => __('admin.products.bulk_activate_confirm'), 'body' => ['action' => 'activate']];
            $actions[] = ['key' => 'deactivate', 'label' => __('admin.products.bulk_deactivate'), 'url' => $url, 'danger' => false, 'confirm' => __('admin.products.bulk_deactivate_confirm'), 'body' => ['action' => 'deactivate']];
        }

        if ($this->viewer->can('catalog.manage')) {
            $actions[] = ['key' => 'delete', 'label' => __('admin.common.delete'), 'url' => $url, 'danger' => true, 'confirm' => __('admin.products.bulk_delete_confirm'), 'body' => ['action' => 'delete']];
        }

        return $actions;
    }

    protected function query(): Builder
    {
        return Product::query()->select('products.*')->with(['gamingPlatform:id,name', 'primaryCategory:id,name', 'productImages' => static fn ($images) => $images->orderBy('sort_order')->orderBy('id'), 'productImages.media'])->withCount('productVariants as variants_count');
    }

    /**
     * @param  Product  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.catalog.products.edit', ['product' => $row->public_id]);

        return [
            'image' => Cell::thumbnail($row->productImages->first()?->media, (string) $row->name),
            'name' => Cell::link($edit, (string) $row->name),
            'sku' => Cell::mono($row->sku),
            'platform' => e((string) ($row->gamingPlatform?->name ?? '—')),
            'category' => e((string) ($row->primaryCategory?->name ?? '—')),
            'status' => Cell::status('product_status', $row->status?->value),
            'variants' => (string) $row->getAttribute('variants_count'),
            'featured' => Cell::yesNo((bool) $row->is_featured),
            'updated' => $this->dateTime($row->updated_at),
        ];
    }
}
