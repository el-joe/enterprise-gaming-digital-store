<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Support\Collection;

/**
 * Reads of the admin product editor (M15): the product with what the page shows, every
 * delivery field definition, and the supplier mappings of its variants. Fixed queries.
 */
class ProductAdminReader
{
    public function forEditor(Product $product): Product
    {
        return $product->load([
            'gamingPlatform:id,name', 'categories:id', 'regions:id', 'productDeliveryFields',
            'productVariants' => static fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'productVariants.region:id,code,name',
            'productImages' => static fn ($query) => $query->orderBy('sort_order'),
            'productImages.media',
        ]);
    }

    /**
     * @return Collection<int, DeliveryFieldDefinition>
     */
    public function deliveryFieldDefinitions(): Collection
    {
        return DeliveryFieldDefinition::query()->orderBy('label')->get(['id', 'code', 'label', 'input_type', 'validation_regex']);
    }

    /**
     * @return Collection<int, SupplierProductMapping>
     */
    public function mappings(?Product $product): Collection
    {
        $variantIds = $product?->productVariants->pluck('id')->all() ?? [];

        return $variantIds === [] ? new Collection : SupplierProductMapping::query()->whereIn('product_variant_id', $variantIds)
            ->with(['supplier:id,name', 'supplierProduct:id,external_id,name'])->orderBy('priority')->get();
    }
}
