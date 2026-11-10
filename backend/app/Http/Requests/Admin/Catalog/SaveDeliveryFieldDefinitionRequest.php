<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Rules\CompilableRegex;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveDeliveryFieldDefinitionRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [$this->presence(), 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('delivery_field_definitions', 'code')->ignore($this->route('deliveryFieldDefinition'))],
            'label' => [$this->presence(), 'string', 'max:120'],
            'input_type' => [$this->presence(), Rule::enum(DeliveryFieldDefinitionInputType::class)],
            'validation_regex' => ['sometimes', 'nullable', 'string', 'max:255', new CompilableRegex],
            'options' => ['required_if:input_type,select', 'nullable', 'array', 'max:200'],
            'options.*.value' => ['required', 'string', 'max:190'],
            'options.*.label' => ['required', 'string', 'max:190'],
            'help_text' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
