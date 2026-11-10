<?php

namespace App\Http\Requests\Admin\Payments;

use App\Domains\Payments\Enums\RefundReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRefundRequest extends FormRequest
{
    /**
     * Staff choose the units (BD4: admin discretion). `duplicate` refunds are created by the
     * system only.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'unit_ids' => ['required', 'array', 'min:1', 'max:100'],
            'unit_ids.*' => ['required', 'string', 'ulid', 'distinct'],
            'reason' => ['required', Rule::enum(RefundReason::class)->except([RefundReason::Duplicate])],
            'note' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
