<?php

namespace App\Http\Requests\Admin\Fulfillment;

use App\Domains\Payments\Enums\RefundReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectOrderRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'refund_reason' => ['sometimes', Rule::enum(RefundReason::class)->only([RefundReason::Fraud, RefundReason::Admin, RefundReason::CustomerRequest])],
        ];
    }

    public function refundReason(): RefundReason
    {
        return RefundReason::from((string) ($this->validated('refund_reason') ?? RefundReason::Fraud->value));
    }
}
