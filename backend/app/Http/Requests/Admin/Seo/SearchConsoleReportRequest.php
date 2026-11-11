<?php

namespace App\Http\Requests\Admin\Seo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchConsoleReportRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'days' => ['sometimes', 'integer', Rule::in([7, 28, 90])],
        ];
    }
}
