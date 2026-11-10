<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductCountryRestrictionMode;
use App\Domains\Shared\Rules\Slug;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveProductRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gaming_platform_id' => [$this->presence(), 'integer', Rule::exists('gaming_platforms', 'id')->whereNull('deleted_at')],
            'primary_category_id' => [$this->presence(), 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => [$this->presence(), 'string', 'max:190'],
            'slug' => [$this->presence(), 'string', 'max:210', new Slug, Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'sku' => [$this->presence(), 'string', 'max:64', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'terms' => ['sometimes', 'nullable', 'string'],
            'delivery_type' => [$this->presence(), Rule::enum(DeliveryType::class)],
            'delivery_time_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_quantity_per_order' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_trending' => ['sometimes', 'boolean'],
            'category_ids' => ['sometimes', 'array', 'max:50'],
            'category_ids.*' => ['integer', 'distinct', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'region_ids' => ['sometimes', 'array', 'max:50'],
            'region_ids.*' => ['integer', 'distinct', Rule::exists('regions', 'id')],
            'delivery_fields' => ['sometimes', 'array', 'max:20'],
            'delivery_fields.*.delivery_field_definition_id' => ['required', 'integer', 'distinct', Rule::exists('delivery_field_definitions', 'id')],
            'delivery_fields.*.is_required' => ['sometimes', 'boolean'],
            'delivery_fields.*.sort_order' => ['sometimes', 'integer'],
            'country_restrictions' => ['sometimes', 'array', 'max:300'],
            'country_restrictions.*.country_code' => ['required', 'string', 'size:2', 'distinct', Rule::exists('countries', 'code')],
            'country_restrictions.*.mode' => ['required', Rule::enum(ProductCountryRestrictionMode::class)],
            'attribute_values' => ['sometimes', 'array', 'max:100'],
            'attribute_values.*.attribute_definition_id' => ['required', 'integer', 'distinct', Rule::exists('attribute_definitions', 'id')],
            'attribute_values.*.value' => ['required', 'string', 'max:255'],
        ];
    }
}
