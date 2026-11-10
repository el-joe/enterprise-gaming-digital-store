<?php

namespace App\Http\Requests\Mcp;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SupplierFilterRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['supplier' => ['sometimes', 'nullable', 'string', 'max:120']];
    }
}
