<?php

namespace App\Http\Requests\Mcp;

use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Mcp\Services\McpActionRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Validates a write tool's arguments with the rules its action declares. Unknown keys are dropped.
 */
class WriteActionRequest extends FormRequest
{
    public function tool(): McpWriteTool
    {
        return McpWriteTool::from(Str::after((string) $this->route()?->getName(), 'mcp.'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return app(McpActionRegistry::class)->for($this->tool())->rules();
    }
}
