<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\HtmlSanitizer;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends CrudService<Product>
 */
class ProductService extends CrudService
{
    public function __construct(
        ListQueryApplier $lists,
        AuditLogger $audit,
        private ProductRepository $products,
        private HtmlSanitizer $sanitizer,
        private SearchIndexer $search,
        private PricingDirtyMarker $dirty,
    ) {
        parent::__construct($lists, $audit);
    }

    public function list(ListQuery $list): LengthAwarePaginator
    {
        return $this->products->adminPage($list);
    }

    protected function model(): string
    {
        return Product::class;
    }

    protected function auditPrefix(): string
    {
        return 'catalog.product';
    }

    protected function relations(): array
    {
        return ['gamingPlatform:id,name,slug', 'primaryCategory:id,name,slug', 'categories:id,name', 'regions:id,code,name', 'productDeliveryFields', 'productCountryRestrictions', 'productAttributeValues'];
    }

    protected function auditIgnored(): array
    {
        return ['description', 'instructions', 'terms'];
    }

    /**
     * @param  Product  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        if (array_key_exists('description', $attributes)) {
            $attributes['description'] = $this->sanitizer->sanitize($attributes['description']);
        }

        return $attributes;
    }

    /**
     * @param  Product  $model
     */
    protected function applyGuarded(Model $model, array $data): void
    {
        if (! $model->exists) {
            $model->forceFill(['status' => ProductStatus::Draft]);
        }
    }

    /**
     * @param  Product  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->syncAssociations($model, $attributes);

        if ($model->wasChanged(['gaming_platform_id', 'primary_category_id']) || array_key_exists('category_ids', $attributes)) {
            $this->dirty->forProduct($model->id);
        }

        $this->search->indexProduct($model->unsetRelations());
    }

    /**
     * @param  Product  $model
     */
    protected function afterDelete(Model $model): void
    {
        ProductVariant::query()->where('product_id', $model->id)->delete();
        $this->search->remove($model);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function syncAssociations(Product $product, array $attributes): void
    {
        if (array_key_exists('category_ids', $attributes)) {
            $product->categories()->sync($attributes['category_ids']);
        }

        if (array_key_exists('region_ids', $attributes)) {
            $product->regions()->sync($attributes['region_ids']);
        }

        if (array_key_exists('delivery_fields', $attributes)) {
            $product->productDeliveryFields()->delete();
            $product->productDeliveryFields()->createMany($attributes['delivery_fields']);
        }

        if (array_key_exists('country_restrictions', $attributes)) {
            $product->productCountryRestrictions()->delete();
            $product->productCountryRestrictions()->createMany($attributes['country_restrictions']);
        }

        if (array_key_exists('attribute_values', $attributes)) {
            $product->productAttributeValues()->delete();
            $product->productAttributeValues()->createMany($attributes['attribute_values']);
        }
    }
}
