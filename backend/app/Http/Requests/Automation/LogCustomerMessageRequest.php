<?php

namespace App\Http\Requests\Automation;

use App\Domains\Automation\Enums\CustomerMessageStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /customers/messages/log. Holds no message text or contact details.
 */
class LogCustomerMessageRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dedupe_key' => ['required', 'string', 'max:128', 'regex:/^D0[1-9]:[A-Za-z0-9:._-]+$/'],
            'customer_id' => ['required', 'string', 'ulid'],
            'status' => ['required', Rule::enum(CustomerMessageStatus::class)],
            'provider_message_id' => ['sometimes', 'nullable', 'string', 'max:128'],
            'error' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
