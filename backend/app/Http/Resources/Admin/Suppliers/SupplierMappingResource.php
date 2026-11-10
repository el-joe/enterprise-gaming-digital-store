<?php

namespace App\Http\Resources\Admin\Suppliers;

use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupplierProductMapping
 */
class SupplierMappingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier', fn (): array => ['id' => $this->supplier->id, 'name' => $this->supplier->name, 'adapter_key' => $this->supplier->adapter_key]),
            'supplier_product_id' => $this->supplier_product_id,
            'supplier_product' => $this->whenLoaded('supplierProduct', fn (): ?array => $this->supplierProduct === null ? null : ['id' => $this->supplierProduct->id, 'external_id' => $this->supplierProduct->external_id, 'name' => $this->supplierProduct->name]),
            'role' => $this->role?->value,
            'priority' => $this->priority,
            'supplier_unit_price' => $this->supplier_unit_price,
            'supplier_unit_currency' => $this->supplier_unit_currency,
            'override_cost' => $this->override_cost,
            'override_cost_currency' => $this->override_cost_currency,
            'allowed_countries' => $this->allowed_countries,
            'denied_countries' => $this->denied_countries,
            'is_active' => $this->is_active,
            'availability' => $this->availability?->value,
            'last_verified_at' => $this->last_verified_at?->toIso8601String(),
        ];
    }
}
