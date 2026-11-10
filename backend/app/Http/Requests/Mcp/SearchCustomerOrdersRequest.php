<?php

namespace App\Http\Requests\Mcp;

/**
 * Exactly one exact identifier: a full email address or an order number. No partial matching,
 * so MCP cannot be used to enumerate customers.
 */
class SearchCustomerOrdersRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['required_without:order_number', 'prohibits:order_number', 'nullable', 'string', 'email:rfc', 'max:190'],
            'order_number' => ['required_without:email', 'nullable', 'string', 'max:24'],
        ];
    }
}
