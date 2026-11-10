<?php

namespace App\Http\Requests\Admin\Mcp;

use App\Domains\Mcp\Enums\McpScope;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueMcpTokenRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9 _.-]+$/'],
            'scopes' => ['sometimes', 'array', 'max:20'],
            'scopes.*' => ['string', 'distinct', Rule::enum(McpScope::class)],
            'allow_writes' => ['sometimes', 'boolean'],
            'rate_limit_per_minute' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:600'],
            'allowed_ips' => ['sometimes', 'nullable', 'array', 'max:20'],
            'allowed_ips.*' => ['string', 'max:45', 'regex:/^[0-9A-Fa-f:.]+(\/\d{1,3})?$/'],
            'expires_in_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    /**
     * @return list<McpScope>
     */
    public function scopes(): array
    {
        return array_map(static fn (string $scope): McpScope => McpScope::from($scope), $this->validated('scopes', []));
    }
}
