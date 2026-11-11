<?php

namespace App\Http\Requests\Admin\Customers;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetRiskRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'risk_level' => ['required', Rule::enum(CustomerRiskLevel::class)],
            'risk_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
