<?php

namespace App\Http\Requests\Admin\Suppliers;

use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\Rules\DecimalString;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveSupplierRequest extends AdminSaveRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [$this->presence(), 'string', 'max:120'],
            'adapter_key' => [$this->presence(), 'string', 'max:64'],
            'status' => ['sometimes', Rule::enum(SupplierStatus::class)],
            'mode' => ['sometimes', Rule::enum(EnvironmentMode::class)],
            'base_url_override' => ['sometimes', 'nullable', 'string', 'max:255', 'url:https'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'fallback_enabled' => ['sometimes', 'boolean'],
            'fallback_supplier_id' => ['sometimes', 'nullable', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'max_fallback_attempts' => ['sometimes', 'integer', 'min:0', 'max:5'],
            'connect_timeout_ms' => ['sometimes', 'integer', 'min:1000', 'max:30000'],
            'request_timeout_ms' => ['sometimes', 'integer', 'min:1000', 'max:60000'],
            'retry_policy' => ['sometimes', 'nullable', 'array:max_attempts'],
            'retry_policy.max_attempts' => ['required_with:retry_policy', 'integer', 'min:0', 'max:3'],
            'verification_deadline_minutes' => ['sometimes', 'integer', 'min:5', 'max:10080'],
            'per_request_cap_base' => ['sometimes', 'nullable', DecimalString::positiveMoney()],
            'low_balance_threshold' => ['sometimes', 'nullable', DecimalString::money()],
        ];
    }
}
