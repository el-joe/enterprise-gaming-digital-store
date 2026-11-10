<?php

namespace App\Http\Requests\Mcp;

class PendingOrdersRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'older_than_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:43200']];
    }

    public function olderThanMinutes(): int
    {
        return (int) ($this->validated('older_than_minutes') ?? 30);
    }
}
