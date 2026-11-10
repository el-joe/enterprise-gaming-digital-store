<?php

namespace App\Http\Requests\Admin\Customers;

use App\Domains\Shared\Rules\DecimalString;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdjustWalletRequest extends FormRequest
{
    /**
     * A staff credit or debit in the wallet currency, with a reason and an `Idempotency-Key`
     * header (8 to 128 of `A-Za-z0-9_-:.`), so a retried request posts once.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'direction' => ['required', 'in:credit,debit'],
            'amount' => ['required', DecimalString::positiveMoney()],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:128', 'regex:/^[A-Za-z0-9_\-:.]+$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }
}
