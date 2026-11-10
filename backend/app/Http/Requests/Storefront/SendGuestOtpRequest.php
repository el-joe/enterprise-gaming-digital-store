<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendGuestOtpRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_public_id' => ['required', 'string', 'size:26', 'alpha_num:ascii'],
            'email' => ['required', 'email:rfc', 'max:190'],
        ];
    }
}
