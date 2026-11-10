<?php

namespace App\Http\Requests\Admin\Currency;

use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SetRateOverrideRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rate' => ['required', DecimalString::rate()],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
