<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A write-only secret: a new value, or null to clear it. Its own rules apply in the service.
 */
class SetSecretRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['present', 'nullable', 'string', 'max:1000'],
        ];
    }
}
