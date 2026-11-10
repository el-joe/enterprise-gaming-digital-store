<?php

namespace App\Http\Requests\Admin\Mcp;

use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Http\Requests\Mcp\McpPageRequest;
use Illuminate\Validation\Rule;

class ListMcpActionsRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'status' => ['sometimes', 'nullable', Rule::enum(McpPendingActionStatus::class)]];
    }

    public function status(): ?McpPendingActionStatus
    {
        $status = $this->validated('status');

        return $status === null ? null : McpPendingActionStatus::from($status);
    }
}
