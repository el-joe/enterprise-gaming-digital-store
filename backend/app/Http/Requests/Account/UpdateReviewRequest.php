<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    /**
     * PATCH /account/reviews/{review} (M13). Ownership is checked by the service (another
     * customer's review is 404).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['sometimes', 'nullable', 'string', 'max:190'],
            'body' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
