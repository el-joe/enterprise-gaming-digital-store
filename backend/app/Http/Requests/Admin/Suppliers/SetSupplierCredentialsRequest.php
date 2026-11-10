<?php

namespace App\Http\Requests\Admin\Suppliers;

use App\Domains\Shared\Enums\EnvironmentMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetSupplierCredentialsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::enum(EnvironmentMode::class)],
            'credentials' => ['required', 'array', 'min:1', 'max:10'],
            'credentials.*' => ['required', 'string', 'max:500'],
            'webhook_secret' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
