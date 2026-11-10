<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RetryPaymentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gateway' => ['sometimes', 'nullable', 'string', 'max:32', 'alpha_dash:ascii'],
            'method' => ['sometimes', 'nullable', 'string', 'max:64', 'alpha_dash:ascii'],
        ];
    }
}
