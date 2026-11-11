<?php

namespace App\Http\Requests\Mobile\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/mobile/auth/biometric-key (OD-1, ADR-040): the device's ECDSA P-256 public key,
 * base64 of a DER SubjectPublicKeyInfo or of the 65-byte uncompressed point.
 */
class RegisterBiometricKeyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'public_key' => ['required', 'string', 'max:512', 'regex:/^[A-Za-z0-9+\/_=-]+$/'],
            'algorithm' => ['sometimes', 'string', 'in:ES256'],
        ];
    }
}
