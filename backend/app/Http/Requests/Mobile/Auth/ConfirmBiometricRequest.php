<?php

namespace App\Http\Requests\Mobile\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/mobile/auth/confirm-biometric (OD-1, ADR-040): the challenge exactly as issued and
 * the base64 DER ECDSA signature of its UTF-8 bytes (SHA-256).
 */
class ConfirmBiometricRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'challenge' => ['required', 'string', 'max:200'],
            'signature' => ['required', 'string', 'max:256', 'regex:/^[A-Za-z0-9+\/_=-]+$/'],
        ];
    }
}
