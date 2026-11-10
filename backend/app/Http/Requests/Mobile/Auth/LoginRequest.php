<?php

namespace App\Http\Requests\Mobile\Auth;

use App\Domains\Identity\DTOs\MobileLoginData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }

        if (is_string($this->input('device_name'))) {
            $this->merge(['device_name' => trim($this->input('device_name'))]);
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
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['required', 'string', 'max:100', 'regex:/^[^\p{C}]+$/u'],
        ];
    }

    public function toData(): MobileLoginData
    {
        return new MobileLoginData(
            $this->string('email')->toString(),
            $this->string('password')->toString(),
            $this->string('device_name')->toString(),
            $this->ip(),
        );
    }
}
