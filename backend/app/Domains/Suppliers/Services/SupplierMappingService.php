<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Suppliers\Adapters\InternalStockAdapter;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
    public const int BULK_LIMIT = 200;

    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private PricingDirtyMarker $dirty)
    {
        parent::__construct($lists, $audit);
    }

    /**
     * Bulk mapping (M15): one line per variant, `variant_sku,external_id,price,currency[,role][,priority]`,
     * all mapped to products of one supplier in one transaction (nothing is saved when any line
     * is refused). Each mapping is created, checked and audited like a single one. Errors are
     * reported per line on `rows`.
     *
     * @return int the number of mappings created
     *
     * @throws ValidationException
     */
    public function bulkCreate(int $supplierId, string $rows): int
    {
        $supplier = Supplier::query()->findOrFail($supplierId);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $rows) ?: []), static fn (string $line): bool => $line !== '' && ! str_starts_with($line, '#')));

        if ($lines === [] || count($lines) > self::BULK_LIMIT) {
            throw ValidationException::withMessages(['rows' => [__('admin.mappings_bulk.err_count', ['max' => self::BULK_LIMIT])]]);
        }

        $parsed = [];
        $errors = [];

        foreach ($lines as $index => $line) {
            $number = $index + 1;
            $cells = array_map('trim', str_getcsv($line));
            [$sku, $externalId, $price, $currency] = array_pad($cells, 4, '');
            $role = ($cells[4] ?? '') !== '' ? $cells[4] : SupplierProductMappingRole::Secondary->value;
            $priority = ($cells[5] ?? '') !== '' ? $cells[5] : '100';

            $variantId = ProductVariant::query()->where('sku', $sku)->value('id');
            $productId = SupplierProduct::query()->where('supplier_id', $supplier->id)->where('external_id', $externalId)->value('id');

            $error = match (true) {
                $variantId === null => __('admin.mappings_bulk.err_variant', ['line' => $number, 'sku' => $sku]),
                $productId === null && $supplier->adapter_key !== InternalStockAdapter::KEY => __('admin.mappings_bulk.err_product', ['line' => $number, 'id' => $externalId]),
                preg_match('/^\d{1,12}(\.\d{1,6})?$/', $price) !== 1 || bccomp($price, '0', 6) <= 0 => __('admin.mappings_bulk.err_price', ['line' => $number]),
                preg_match('/^[A-Z]{3}$/', strtoupper($currency)) !== 1 => __('admin.mappings_bulk.err_currency', ['line' => $number]),
                SupplierProductMappingRole::tryFrom($role) === null => __('admin.mappings_bulk.err_role', ['line' => $number]),
                ! ctype_digit($priority) || (int) $priority > 65535 => __('admin.mappings_bulk.err_priority', ['line' => $number]),
                default => null,
            };

            if ($error !== null) {
                $errors[] = $error;

                continue;
            }

            $parsed[$number] = array_filter([
                'product_variant_id' => (int) $variantId,
                'supplier_id' => $supplier->id,
                'supplier_product_id' => $productId,
                'role' => $role,
                'priority' => (int) $priority,
                'supplier_unit_price' => $price,
                'supplier_unit_currency' => strtoupper($currency),
            ], static fn (mixed $value): bool => $value !== null);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['rows' => $errors]);
        }

        DB::transaction(function () use ($parsed): void {
            foreach ($parsed as $number => $attributes) {
                try {
                    $this->create($attributes);
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages(['rows' => [__('admin.mappings_bulk.err_line', ['line' => $number, 'message' => collect($exception->errors())->flatten()->first()])]]);
                }
            }
        });

        return count($parsed);
    }

    /**
     * Switch mappings on or off (M15 bulk action), each audited as an update.
     *
     * @param  list<int>  $ids
     * @return int the number changed
     */
    public function setActive(array $ids, bool $active): int
    {
        $changed = 0;

        DB::transaction(function () use ($ids, $active, &$changed): void {
            SupplierProductMapping::query()->whereKey($ids)->where('is_active', ! $active)->orderBy('id')->lockForUpdate()->get()
                ->each(function (SupplierProductMapping $mapping) use ($active, &$changed): void {
                    $this->update($mapping, ['is_active' => $active]);
                    $changed++;
                });
        });

        return $changed;
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
        $this->attachSupplierImage($model);
    }

    /**
     * A product mapped by hand gets the supplier's image like an imported one (owner decision 12,
     * Storefront v2 "right image on every card"): attached now when it is downloaded; an image
     * downloaded later attaches itself to every mapped product (SupplierImageService::refresh,
     * `suppliers:sync-images`).
     */
    private function attachSupplierImage(SupplierProductMapping $mapping): void
    {
        $source = $mapping->supplierProduct;
        $product = $mapping->productVariant?->product;

        if ($source === null || $product === null) {
            return;
        }

        if ($source->image_media_id !== null) {
            app(SupplierImageService::class)->attach($source, $product);
        }
    }

    /**
     * @param  SupplierProductMapping  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->dirty->forVariants([$model->product_variant_id]);
    }
}
