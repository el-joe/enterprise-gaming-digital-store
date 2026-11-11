<?php

namespace App\Http\Requests\Admin\Tables;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportTableRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'format' => ['required', 'string', Rule::in(['csv', 'xlsx'])],
            'ids' => ['sometimes', 'array', 'max:500'],
            'ids.*' => ['string', 'max:64'],
            ...TableViewRules::rules(),
        ];
    }
}
