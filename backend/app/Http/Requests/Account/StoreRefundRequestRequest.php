<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequestRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_public_id' => ['required', 'string', 'ulid'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'support_ticket_public_id' => ['sometimes', 'nullable', 'string', 'ulid'],
        ];
    }
}
