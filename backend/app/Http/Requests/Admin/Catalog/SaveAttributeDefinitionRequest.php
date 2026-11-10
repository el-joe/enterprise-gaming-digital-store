<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Catalog\Enums\AttributeDefinitionType;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveAttributeDefinitionRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [$this->presence(), 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('attribute_definitions', 'code')->ignore($this->route('attributeDefinition'))],
            'name' => [$this->presence(), 'string', 'max:120'],
            'type' => [$this->presence(), Rule::enum(AttributeDefinitionType::class)],
            'options' => ['required_if:type,select', 'nullable', 'array', 'max:200'],
            'options.*.value' => ['required', 'string', 'max:190'],
            'options.*.label' => ['required', 'string', 'max:190'],
            'is_filterable' => ['sometimes', 'boolean'],
        ];
    }
}
