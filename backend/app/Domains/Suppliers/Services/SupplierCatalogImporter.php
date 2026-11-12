<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Services\ProductService;
use App\Domains\Catalog\Services\ProductVariantService;
use App\Domains\Currency\Models\Currency;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Jobs\SyncSupplierCatalog;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * "Add to store" (supplier catalog screen): turns synced supplier products into draft store
 * products. The category and platform are the staff overrides, else the supplier's category name and
 * brand, found or created by SupplierCategoryResolver and SupplierPlatformResolver. Per supplier product, in one transaction: a draft product (ProductService), one
 * variant per fixed denomination or one custom-amount variant for a range product
 * (ProductVariantService) and a primary mapping per variant (SupplierMappingService), each
 * created and audited by its own service. Costs for the mapped denominations are written by
 * SupplierPriceWriter and each variant is priced by the PricingEngine with the existing rules; a
 * variant the engine cannot price stays inactive with the engine's reason. Supplier products that
 * already have a mapping are not touched again. Images stay empty (no brand logos, BD8).
 */
class SupplierCatalogImporter
{
    public const int LIMIT = 100;

    private const int SCALE = 6;

    /** The delivery field the supplier adapters read when a product needs the customer's user id. */
    private const string USER_ID_FIELD = 'user_id';

    public function __construct(
        private ProductService $products,
        private ProductVariantService $variants,
        private SupplierMappingService $mappings,
        private SupplierPriceWriter $prices,
        private PricingEngine $engine,
        private AuditLogger $audit,
        private SupplierCategoryResolver $categories,
        private SupplierPlatformResolver $platforms,
    ) {}

    /**
     * @param  list<int>  $supplierProductIds
     * @return array{added: int, already: int, skipped: list<array{id: int, name: string, reason: string}>, inactive: list<array{sku: string, reason: string}>, products: list<string>}
     */
    public function addToStore(array $supplierProductIds, ?int $categoryId, ?int $platformId, string $triggeredBy): array
    {
        $result = ['added' => 0, 'already' => 0, 'skipped' => [], 'inactive' => [], 'products' => []];
        $suppliers = [];

        foreach (array_values(array_unique($supplierProductIds)) as $id) {
            $outcome = DB::transaction(fn (): array => $this->addOne($id, $categoryId, $platformId));

            match ($outcome['status']) {
                'added' => $result['added']++,
                'already' => $result['already']++,
                default => $result['skipped'][] = ['id' => $id, 'name' => $outcome['name'], 'reason' => $outcome['reason']],
            };

            if ($outcome['status'] === 'added') {
                $result['products'][] = $outcome['product'];
                $result['inactive'] = [...$result['inactive'], ...$outcome['inactive']];
                $suppliers[$outcome['supplier_id']] = true;
            }
        }

        foreach (array_keys($suppliers) as $supplierId) {
            SyncSupplierCatalog::dispatch($supplierId, 'pricing', $triggeredBy);
        }

        return $result;
    }

    /**
     * @return array{status: string, name: string, reason?: string, product?: string, supplier_id?: int, inactive?: list<array{sku: string, reason: string}>}
     */
    private function addOne(int $id, ?int $categoryId, ?int $platformId): array
    {
        $source = SupplierProduct::query()->whereKey($id)->lockForUpdate()->first();

        if ($source === null) {
            return ['status' => 'skipped', 'name' => '#'.$id, 'reason' => __('admin.supplier_catalog.reason_missing')];
        }

        if ($source->supplierProductMappings()->exists()) {
            return ['status' => 'already', 'name' => $source->name];
        }

        $reason = $this->refusal($source);
        $amounts = $reason === null ? $this->amounts($source) : [];
        $brand = SupplierPlatformResolver::brandOf($source);
        $userIdField = $source->requires_user_id ? DeliveryFieldDefinition::query()->where('code', self::USER_ID_FIELD)->value('id') : null;

        $reason ??= match (true) {
            $source->denomination_type === DenominationType::Fixed && $amounts === [] => __('admin.supplier_catalog.reason_no_amounts'),
            $platformId === null && $brand === null => __('admin.supplier_catalog.reason_no_brand'),
            $source->requires_user_id && $userIdField === null => __('admin.supplier_catalog.reason_user_id'),
            $categoryId === null && trim((string) $source->category_name) === '' => __('admin.supplier_catalog.reason_no_category'),
            default => null,
        };

        if ($reason !== null) {
            return ['status' => 'skipped', 'name' => $source->name, 'reason' => $reason];
        }

        $categoryId ??= $this->categories->resolve((string) $source->category_name)?->id;
        $platform = $platformId ?? $this->platforms->resolve((string) $brand)?->id;

        $product = $this->products->create([
            'gaming_platform_id' => $platform,
            'primary_category_id' => $categoryId,
            'category_ids' => [$categoryId],
            'name' => Str::limit($source->name, 190, ''),
            'slug' => $this->uniqueSlug($source),
            'sku' => $this->uniqueProductSku($source),
            'description' => $this->rawText($source, ['description']),
            'instructions' => $this->rawText($source, ['redeemInstruction.verbose', 'redeemInstruction.concise']),
            'delivery_type' => DeliveryType::InstantCode->value,
            'delivery_fields' => $userIdField === null ? [] : [['delivery_field_definition_id' => $userIdField, 'is_required' => true, 'sort_order' => 0]],
        ]);

        $created = $source->denomination_type === DenominationType::Range
            ? [$this->createVariant($product, $source, null, 0)]
            : array_map(fn (string $amount, int $index): ProductVariant => $this->createVariant($product, $source, $amount, $index), $amounts, array_keys($amounts));

        $this->prices->writeFor($source);
        $inactive = $this->price($created);

        $this->audit->record(new AuditEntry(
            action: 'suppliers.catalog.added_to_store',
            subject: $product,
            after: ['supplier_product_id' => $source->id, 'supplier_id' => $source->supplier_id, 'external_id' => $source->external_id, 'variants' => count($created), 'inactive' => $inactive],
        ));

        return ['status' => 'added', 'name' => $source->name, 'product' => $product->public_id, 'supplier_id' => $source->supplier_id, 'inactive' => $inactive];
    }

    /**
     * Why a supplier product cannot become a store product, or null.
     */
    private function refusal(SupplierProduct $source): ?string
    {
        return match (true) {
            $source->removed_at !== null => __('admin.supplier_catalog.reason_removed'),
            $source->denomination_type === null => __('admin.supplier_catalog.reason_no_type'),
            $source->currency === null || ! Currency::query()->where('code', $source->currency)->exists() => __('admin.supplier_catalog.reason_currency', ['currency' => $source->currency ?? '—']),
            $source->denomination_type === DenominationType::Range && ($source->min_denomination === null || $source->max_denomination === null || bccomp($source->max_denomination, $source->min_denomination, self::SCALE) !== 1) => __('admin.supplier_catalog.reason_no_range'),
            default => null,
        };
    }

    /**
     * Fixed face values, normalised to six decimals, ascending: the synced list, else the keys
     * of the cost map.
     *
     * @return list<string>
     */
    private function amounts(SupplierProduct $source): array
    {
        if ($source->denomination_type !== DenominationType::Fixed) {
            return [];
        }

        $listed = is_array($source->fixed_denominations) && $source->fixed_denominations !== []
            ? $source->fixed_denominations
            : array_keys(array_filter(is_array($source->cost_map) ? $source->cost_map : [], static fn (mixed $value, int|string $key): bool => $key !== 'rate', ARRAY_FILTER_USE_BOTH));

        $amounts = [];

        foreach ($listed as $value) {
            $value = (string) $value;

            if (preg_match('/^\d{1,14}(\.\d{1,6})?$/', $value) === 1 && bccomp($value, '0', self::SCALE) === 1) {
                $amounts[bcadd($value, '0', self::SCALE)] = true;
            }
        }

        $amounts = array_map('strval', array_keys($amounts));
        usort($amounts, static fn (string $a, string $b): int => bccomp($a, $b, self::SCALE));

        return $amounts;
    }

    private function createVariant(Product $product, SupplierProduct $source, ?string $amount, int $index): ProductVariant
    {
        $range = $amount === null;
        $face = $range ? (string) $source->min_denomination : $amount;
        $title = $range
            ? $this->trim((string) $source->min_denomination).'–'.$this->trim((string) $source->max_denomination).' '.$source->currency
            : $this->trim($amount).' '.$source->currency;

        $variant = $this->variants->createFor($product, [
            'sku' => $this->uniqueVariantSku($product->sku.'-'.($range ? 'RANGE' : $this->trim($amount))),
            'title' => $title,
            'denomination_value' => $range ? null : $amount,
            'denomination_currency' => $source->currency,
            'is_custom_amount' => $range,
            'custom_min' => $range ? $source->min_denomination : null,
            'custom_max' => $range ? $source->max_denomination : null,
            'availability' => ($source->availability === SupplierAvailability::Unavailable ? ProductVariantAvailability::OutOfStock : ProductVariantAvailability::InStock)->value,
            'is_active' => false,
            'sort_order' => $index,
        ]);

        $this->mappings->create([
            'product_variant_id' => $variant->id,
            'supplier_id' => $source->supplier_id,
            'supplier_product_id' => $source->id,
            'role' => SupplierProductMappingRole::Primary->value,
            'priority' => 100,
            'supplier_unit_price' => $face,
            'supplier_unit_currency' => $source->currency,
            'is_active' => true,
            'availability' => ($source->availability ?? SupplierAvailability::Unknown)->value,
        ]);

        return $variant;
    }

    /**
     * Price every new variant with the existing engine and rules; a variant that gets a
     * purchasable listing price is switched on, every other one stays off with the reason.
     *
     * @param  list<ProductVariant>  $created
     * @return list<array{sku: string, reason: string}>
     */
    private function price(array $created): array
    {
        $fresh = ProductVariant::query()->whereKey(array_map(static fn (ProductVariant $variant): int => $variant->id, $created))->orderBy('id')->get();
        $quotes = $this->engine->quoteMany($fresh, new PricingContext);
        $inactive = [];

        foreach ($fresh as $variant) {
            $quote = $quotes[$variant->id];

            if (! $quote->purchasable || $quote->baseAmount === null) {
                $inactive[] = ['sku' => $variant->sku, 'reason' => (string) ($quote->blockReason ?? 'price_unavailable')];

                continue;
            }

            $this->variants->update($variant, ['is_active' => true]);
            $variant->forceFill(['price_base' => $quote->baseAmount->amount, 'pricing_dirty_at' => null])->save();
        }

        return $inactive;
    }

    private function uniqueSlug(SupplierProduct $source): string
    {
        $base = Str::limit(Str::slug($source->name), 180, '') ?: 'product';
        $base = rtrim($base, '-');

        foreach ([$base, $base.'-'.Str::lower((string) $source->country_code), $base.'-'.$source->id] as $candidate) {
            $candidate = trim($candidate, '-');

            if (! Product::withTrashed()->where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base.'-'.Str::lower(Str::random(6));
    }

    private function uniqueProductSku(SupplierProduct $source): string
    {
        $sku = Str::limit('SP'.$source->supplier_id.'-'.Str::upper((string) preg_replace('/[^A-Za-z0-9_-]/', '', $source->external_id)), 50, '');

        return Product::withTrashed()->where('sku', $sku)->exists() ? $sku.'-'.$source->id : $sku;
    }

    private function uniqueVariantSku(string $sku): string
    {
        $sku = Str::limit($sku, 70, '');

        return ProductVariant::withTrashed()->where('sku', $sku)->exists() ? $sku.'-'.Str::upper(Str::random(4)) : $sku;
    }

    /**
     * The first non-empty string among the given keys of the supplier's raw record.
     *
     * @param  list<string>  $keys
     */
    private function rawText(SupplierProduct $source, array $keys): ?string
    {
        $raw = is_array($source->raw) ? $source->raw : [];

        foreach ($keys as $key) {
            $value = data_get($raw, $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function trim(string $amount): string
    {
        return str_contains($amount, '.') ? rtrim(rtrim($amount, '0'), '.') : $amount;
    }
}
