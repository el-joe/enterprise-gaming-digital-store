<?php

namespace App\Http\Requests\Admin\Payments;

use App\Domains\Shared\Enums\EnvironmentMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetPublicKeyRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::enum(EnvironmentMode::class)],
            'value' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }
}
