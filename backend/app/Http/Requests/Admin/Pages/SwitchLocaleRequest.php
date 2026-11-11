<?php

namespace App\Http\Requests\Admin\Pages;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchLocaleRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::in((array) config('app.supported_locales'))],
        ];
    }
}
