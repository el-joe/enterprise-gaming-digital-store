<?php

namespace App\Http\Requests\Admin\Cms;

use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveMenuRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location' => [$this->presence(), 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('menus', 'location')->ignore($this->route('menu'))],
            'name' => [$this->presence(), 'string', 'max:120'],
        ];
    }
}
