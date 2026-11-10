<?php

namespace App\Http\Requests\Admin\Support;

use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideRefundRequestRequest extends FormRequest
{
    /**
     * Approve names the units to refund, as for a staff refund (M10); reject needs only a note.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $approving = $this->routeIs('*.approve');

        return [
            'unit_ids' => [$approving ? 'required' : 'prohibited', 'array', 'min:1', 'max:100'],
            'unit_ids.*' => ['required', 'string', 'ulid', 'distinct'],
            'reason' => [$approving ? 'required' : 'prohibited', Rule::enum(RefundReason::class)->except([RefundReason::Duplicate])],
            'destination' => ['sometimes', Rule::enum(RefundDestination::class)],
            'note' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
