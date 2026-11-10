<?php

namespace App\Http\Requests\Storefront;

use App\Domains\Shared\Rules\SafeUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AffiliateClickRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9_-]+$/'],
            'landing_url' => ['sometimes', 'nullable', 'string', 'max:500', new SafeUrl],
        ];
    }
}
