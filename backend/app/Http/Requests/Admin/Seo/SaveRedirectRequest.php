<?php

namespace App\Http\Requests\Admin\Seo;

use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveRedirectRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_path' => [$this->presence(), 'string', 'max:500', 'regex:~^/(?!/)~'],
            'target_path' => ['sometimes', 'nullable', 'string', 'max:500', 'regex:~^/(?!/)~'],
            'status_code' => [$this->presence(), 'integer', Rule::in([301, 302, 410])],
            'is_active' => [$this->presence(), 'boolean'],
        ];
    }
}
