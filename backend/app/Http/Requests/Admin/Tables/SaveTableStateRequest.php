<?php

namespace App\Http\Requests\Admin\Tables;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTableStateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hidden_columns' => ['present', 'array', 'max:50'],
            'hidden_columns.*' => ['string', 'max:64'],
            'page_length' => ['required', 'integer', Rule::in((array) config('admin.tables.page_lengths'))],
        ];
    }
}
