<?php

namespace App\Http\Requests\Admin\Suppliers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Bulk supplier mapping (M15): the supplier and the lines to map; each line is checked by
 * SupplierMappingService::bulkCreate.
 */
class BulkMappingRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'rows' => ['required', 'string', 'max:40000'],
        ];
    }
}
