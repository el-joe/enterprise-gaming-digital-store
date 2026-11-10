<?php

namespace App\Http\Requests\Storefront\Auth;

use App\Domains\Identity\DTOs\RegistrationData;
use App\Domains\Identity\Services\PasswordPolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::forCustomer()],
        ];
    }

    public function toData(): RegistrationData
    {
        return new RegistrationData(
            $this->string('name')->toString(),
            $this->string('email')->toString(),
            $this->string('password')->toString(),
        );
    }
}
