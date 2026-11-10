<?php

namespace App\Http\Requests\Storefront\Auth;

use App\Domains\Identity\DTOs\PasswordResetData;
use App\Domains\Identity\Services\PasswordPolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::forCustomer()],
        ];
    }

    public function toData(): PasswordResetData
    {
        return new PasswordResetData(
            $this->string('email')->toString(),
            $this->string('token')->toString(),
            $this->string('password')->toString(),
        );
    }
}
