<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Shared\Rules\DecimalString;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveProductVariantRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'region_id' => ['sometimes', 'nullable', 'integer', Rule::exists('regions', 'id')],
            'sku' => [$this->presence(), 'string', 'max:80', Rule::unique('product_variants', 'sku')->ignore($this->route('productVariant'))],
            'title' => [$this->presence(), 'string', 'max:190'],
            'denomination_value' => ['sometimes', 'nullable', DecimalString::positiveMoney()],
            'denomination_currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::exists('currencies', 'code')],
            'is_custom_amount' => ['sometimes', 'boolean'],
            'custom_min' => ['sometimes', 'nullable', DecimalString::positiveMoney()],
            'custom_max' => ['sometimes', 'nullable', DecimalString::positiveMoney()],
            'manual_cost_base' => ['sometimes', 'nullable', DecimalString::money()],
            'compare_at_base' => ['sometimes', 'nullable', DecimalString::money()],
            'availability' => ['sometimes', Rule::enum(ProductVariantAvailability::class)],
            'is_active' => [$this->presence(), 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
