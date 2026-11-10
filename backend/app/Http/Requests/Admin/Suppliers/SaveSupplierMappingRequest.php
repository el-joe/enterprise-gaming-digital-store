<?php

namespace App\Http\Requests\Admin\Suppliers;

use App\Domains\Shared\Rules\DecimalString;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveSupplierMappingRequest extends AdminSaveRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_variant_id' => [$this->presence(), 'integer', Rule::exists('product_variants', 'id')->whereNull('deleted_at')],
            'supplier_id' => [$this->presence(), 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'supplier_product_id' => ['sometimes', 'integer', Rule::exists('supplier_products', 'id')],
            'role' => [$this->presence(), Rule::enum(SupplierProductMappingRole::class)],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'supplier_unit_price' => [$this->presence(), DecimalString::positiveMoney()],
            'supplier_unit_currency' => [$this->presence(), 'string', 'size:3', 'alpha'],
            'override_cost' => ['sometimes', 'nullable', DecimalString::money(), 'required_with:override_cost_currency'],
            'override_cost_currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::exists('currencies', 'code'), 'required_with:override_cost'],
            'allowed_countries' => ['sometimes', 'nullable', 'array'],
            'allowed_countries.*' => ['string', 'size:2'],
            'denied_countries' => ['sometimes', 'nullable', 'array'],
            'denied_countries.*' => ['string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
            'availability' => ['sometimes', Rule::enum(SupplierAvailability::class)],
        ];
    }
}
