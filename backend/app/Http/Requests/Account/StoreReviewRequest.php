<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * `order_item_id` comes from `GET /reviews/eligible`; ownership and delivery are checked by
     * the service.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_item_id' => ['required', 'integer', 'min:1'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['sometimes', 'nullable', 'string', 'max:190'],
            'body' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
