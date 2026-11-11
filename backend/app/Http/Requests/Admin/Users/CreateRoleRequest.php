<?php

namespace App\Http\Requests\Admin\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[A-Za-z][A-Za-z0-9 _-]*$/', Rule::unique('roles', 'name')->where('guard_name', 'web')],
        ];
    }
}
