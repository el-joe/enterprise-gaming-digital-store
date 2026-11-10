<?php

namespace App\Http\Requests\Mcp;

class SearchProductsRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'query' => ['required', 'string', 'min:2', 'max:100']];
    }
}
