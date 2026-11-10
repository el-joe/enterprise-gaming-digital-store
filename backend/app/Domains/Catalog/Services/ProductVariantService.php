<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @extends CrudService<ProductVariant>
 */
class ProductVariantService extends CrudService
{
    public function __construct(
        ListQueryApplier $lists,
        AuditLogger $audit,
        private SearchIndexer $search,
        private PricingDirtyMarker $dirty,
    ) {
        parent::__construct($lists, $audit);
    }

    public function listFor(Product $product, ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            ProductVariant::query()->with($this->relations())->where('product_id', $product->id),
            $list,
            ['is_active' => 'bool', 'availability' => 'exact', 'region_id' => 'exact'],
            ['id', 'sort_order', 'title', 'sku', 'price_base', 'created_at'],
            ['title', 'sku'],
            'sort_order',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createFor(Product $product, array $attributes): ProductVariant
    {
        return $this->create([...$attributes, 'product_id' => $product->id]);
    }

    protected function model(): string
    {
        return ProductVariant::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.variant';
    }

    protected function relations(): array
    {
        return ['region:id,code,name'];
    }

    /**
     * @param  ProductVariant  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $merged = ['region_id' => null, ...$model->only(['product_id', 'region_id', 'denomination_value', 'denomination_currency', 'is_custom_amount', 'custom_min', 'custom_max']), ...$attributes];

        $this->assertDenominationIsCoherent($merged);
        $this->assertUniqueWithoutRegion($model, $merged);

        return $attributes;
    }

    /**
     * @param  ProductVariant  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        if ($model->wasRecentlyCreated || $model->wasChanged(['manual_cost_base', 'availability', 'is_active'])) {
            $this->dirty->forVariants([$model->id]);
        }

        $this->reindexProduct($model);
    }

    /**
     * @param  ProductVariant  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->reindexProduct($model);
    }

    private function reindexProduct(ProductVariant $variant): void
    {
        $product = Product::query()->find($variant->product_id);

        if ($product !== null) {
            $this->search->indexProduct($product);
        }
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function assertDenominationIsCoherent(array $state): void
    {
        if (! empty($state['is_custom_amount'])) {
            if (empty($state['custom_min']) || empty($state['custom_max'])) {
                throw ValidationException::withMessages(['custom_min' => ['Custom amount variants need a minimum and maximum.']]);
            }

            if (bccomp((string) $state['custom_max'], (string) $state['custom_min'], 6) !== 1) {
                throw ValidationException::withMessages(['custom_max' => ['The maximum must be greater than the minimum.']]);
            }

            return;
        }

        if (empty($state['denomination_value']) || empty($state['denomination_currency'])) {
            throw ValidationException::withMessages(['denomination_value' => ['Fixed variants need a denomination value and currency.']]);
        }
    }

    /**
     * uq_pv_product_region_denomination does not catch repeats when region_id is NULL.
     *
     * @param  array<string, mixed>  $state
     */
    private function assertUniqueWithoutRegion(ProductVariant $variant, array $state): void
    {
        if ($state['region_id'] !== null || empty($state['denomination_value'])) {
            return;
        }

        $exists = ProductVariant::query()
            ->where('product_id', $state['product_id'])
            ->whereNull('region_id')
            ->where('denomination_value', $state['denomination_value'])
            ->where('denomination_currency', $state['denomination_currency'])
            ->when($variant->exists, static fn ($query) => $query->whereKeyNot($variant->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['denomination_value' => ['This denomination already exists for this product.']]);
        }
    }
}
