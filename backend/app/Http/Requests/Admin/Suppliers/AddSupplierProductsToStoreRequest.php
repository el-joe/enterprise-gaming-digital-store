<?php

namespace App\Http\Requests\Admin\Suppliers;

use App\Domains\Suppliers\Services\SupplierCatalogImporter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddSupplierProductsToStoreRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:'.SupplierCatalogImporter::LIMIT],
            'ids.*' => ['integer', 'distinct', 'min:1'],
            'primary_category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'gaming_platform_id' => ['nullable', 'integer', Rule::exists('gaming_platforms', 'id')->whereNull('deleted_at')],
        ];
    }
}
