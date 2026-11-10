<?php

namespace App\Http\Requests\Storefront;

use App\Domains\Shared\Services\CountryDirectory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentOptionsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'size:2', CountryDirectory::rule()],
            'email' => ['sometimes', 'nullable', 'email:rfc', 'max:190'],
        ];
    }
}
