<?php

namespace App\Http\Requests\Admin\Customers;

use App\Domains\Wallet\Enums\CustomerWalletStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetWalletStatusRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(CustomerWalletStatus::class)],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
