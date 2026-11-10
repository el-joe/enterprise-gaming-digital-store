<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Suppliers\Adapters\InternalStockAdapter;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Variant to supplier product mappings (spec Phase 5, multi-supplier mapping). One primary per
 * variant; the supplier product must belong to the supplier. Internal stock has no synced
 * catalog, so its supplier product is created on first mapping. Changes mark the variant's
 * listing price dirty.
 *
 * @extends CrudService<SupplierProductMapping>
 */
class SupplierMappingService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private PricingDirtyMarker $dirty)
    {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return SupplierProductMapping::class;
    }

    protected function auditPrefix(): string
    {
        return 'suppliers.mapping';
    }

    protected function filters(): array
    {
        return ['supplier_id' => 'exact', 'product_variant_id' => 'exact', 'role' => 'exact', 'is_active' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'priority', 'created_at'];
    }

    protected function relations(): array
    {
        return ['supplier:id,name,adapter_key', 'supplierProduct:id,supplier_id,external_id,name'];
    }

    /**
     * @param  SupplierProductMapping  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $supplierId = (int) ($attributes['supplier_id'] ?? $model->supplier_id);
        $variantId = (int) ($attributes['product_variant_id'] ?? $model->product_variant_id);
        $supplier = Supplier::query()->findOrFail($supplierId);

        if (! isset($attributes['supplier_product_id']) && ! $model->exists && $supplier->adapter_key === InternalStockAdapter::KEY) {
            $attributes['supplier_product_id'] = SupplierProduct::query()->firstOrCreate(
                ['supplier_id' => $supplierId, 'external_id' => 'variant-'.$variantId],
                ['name' => 'Internal stock for variant '.$variantId],
            )->id;
        }

        $productId = $attributes['supplier_product_id'] ?? $model->supplier_product_id;

        if ($productId === null || ! SupplierProduct::query()->whereKey($productId)->where('supplier_id', $supplierId)->exists()) {
            throw ValidationException::withMessages(['supplier_product_id' => ['The supplier product must belong to the selected supplier.']]);
        }

        $role = $attributes['role'] ?? $model->role?->value;

        if ($role === SupplierProductMappingRole::Primary->value && SupplierProductMapping::query()
            ->where('product_variant_id', $variantId)->where('role', SupplierProductMappingRole::Primary)
            ->when($model->exists, static fn ($query) => $query->whereKeyNot($model->id))->exists()) {
            throw ValidationException::withMessages(['role' => ['This variant already has a primary supplier.']]);
        }

        if ($model->exists) {
            $this->dirty->forVariants([$model->product_variant_id]);
        }

        return $attributes;
    }

    /**
     * @param  SupplierProductMapping  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->dirty->forVariants([$model->product_variant_id]);
    }

    /**
     * @param  SupplierProductMapping  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->dirty->forVariants([$model->product_variant_id]);
    }
}
