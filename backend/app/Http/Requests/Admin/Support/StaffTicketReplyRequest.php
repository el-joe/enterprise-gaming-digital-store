<?php

namespace App\Http\Requests\Admin\Support;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StaffTicketReplyRequest extends FormRequest
{
    /**
     * `is_internal` makes the message a note visible to staff only.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:10000'],
            'is_internal' => ['sometimes', 'boolean'],
        ];
    }
}
