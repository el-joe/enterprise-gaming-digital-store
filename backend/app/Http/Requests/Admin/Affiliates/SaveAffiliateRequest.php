<?php

namespace App\Http\Requests\Admin\Affiliates;

use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Shared\Rules\DecimalString;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveAffiliateRequest extends AdminSaveRequest
{
    /**
     * `user_id` is accepted on create only. `payout_details` is write-only.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $affiliate = $this->route('affiliate');

        return [
            'user_id' => [Rule::requiredIf($this->isMethod('POST')), Rule::prohibitedIf($this->isMethod('PATCH')), 'integer',
                Rule::exists('users', 'id')->where('type', 'customer'), Rule::unique('affiliates', 'user_id')],
            'code' => [$this->presence(), 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('affiliates', 'code')->ignore($affiliate)],
            'status' => [$this->presence(), Rule::enum(AffiliateStatus::class)],
            'commission_pct' => [$this->presence(), DecimalString::percent()],
            'payout_details' => ['sometimes', 'nullable', 'array:method,account,notes'],
            'payout_details.method' => ['sometimes', 'string', 'max:32'],
            'payout_details.account' => ['sometimes', 'string', 'max:190'],
            'payout_details.notes' => ['sometimes', 'string', 'max:500'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
