<?php

namespace App\Http\Requests\Admin\Seo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Google's OAuth redirect back (M18): `state` and either `code` or `error`. Shape only; the
 * state is compared with the session by SearchConsoleConnection.
 */
class SearchConsoleCallbackRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'state' => ['nullable', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:1000'],
            'error' => ['nullable', 'string', 'max:100'],
        ];
    }
}
