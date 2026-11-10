<?php

namespace App\Http\Requests\Account;

use App\Domains\Identity\Services\PasswordPolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /account/password (M-A5): the current password, and a new one that follows the account's
 * policy (customer or staff, SECURITY.md section 1) and differs from the current one.
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', $this->user()->isStaff() ? PasswordPolicy::forStaff() : PasswordPolicy::forCustomer()],
        ];
    }
}
