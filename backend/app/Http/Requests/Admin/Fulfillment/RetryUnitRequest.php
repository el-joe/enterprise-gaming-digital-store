<?php

namespace App\Http\Requests\Admin\Fulfillment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RetryUnitRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'unit_public_id' => ['required', 'string', 'ulid'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
