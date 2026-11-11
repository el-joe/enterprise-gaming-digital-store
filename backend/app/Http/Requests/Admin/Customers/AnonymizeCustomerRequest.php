<?php

namespace App\Http\Requests\Admin\Customers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Erasure needs a reason and the customer's id typed again, so it is never done by mistake.
 */
class AnonymizeCustomerRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'confirm' => ['required', 'string', Rule::in([(string) $this->route('customer')?->public_id])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['confirm.in' => __('admin.customers.erase_confirm_mismatch')];
    }
}
