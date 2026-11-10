<?php

namespace App\Http\Requests\Account;

use App\Domains\Support\Enums\SupportTicketCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(SupportTicketCategory::class)],
            'subject' => ['required', 'string', 'min:3', 'max:190'],
            'body' => ['required', 'string', 'min:1', 'max:10000'],
            'order_public_id' => ['sometimes', 'nullable', 'string', 'ulid'],
        ];
    }
}
