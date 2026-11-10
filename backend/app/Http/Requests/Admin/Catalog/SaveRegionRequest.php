<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveRegionRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [$this->presence(), 'string', 'max:16', 'alpha_dash:ascii', Rule::unique('regions', 'code')->ignore($this->route('region'))],
            'name' => [$this->presence(), 'string', 'max:80'],
            'is_global' => ['sometimes', 'boolean'],
            'country_codes' => ['sometimes', 'array', 'max:300'],
            'country_codes.*' => ['string', 'size:2', 'distinct', Rule::exists('countries', 'code')],
        ];
    }
}
