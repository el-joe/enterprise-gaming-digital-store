<?php

namespace App\Http\Requests\Admin\Payments;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveReconciliationRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['resolved', 'ignored'])],
            'note' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
