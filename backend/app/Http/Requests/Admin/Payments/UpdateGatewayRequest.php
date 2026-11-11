<?php

namespace App\Http\Requests\Admin\Payments;

use App\Domains\Shared\Enums\EnvironmentMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGatewayRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['sometimes', 'boolean'],
            'mode' => ['sometimes', Rule::enum(EnvironmentMode::class)],
        ];
    }
}
