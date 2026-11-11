<?php

namespace App\Http\Requests\Admin\Tables;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveFilterRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
            'payload' => ['present', 'array'],
            ...TableViewRules::rules('payload.'),
        ];
    }
}
