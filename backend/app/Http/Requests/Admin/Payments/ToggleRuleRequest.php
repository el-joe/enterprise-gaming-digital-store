<?php

namespace App\Http\Requests\Admin\Payments;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ToggleRuleRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
