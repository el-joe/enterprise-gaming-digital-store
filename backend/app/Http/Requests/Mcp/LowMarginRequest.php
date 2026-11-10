<?php

namespace App\Http\Requests\Mcp;

use App\Domains\Shared\Rules\DecimalString;

class LowMarginRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'threshold_pct' => ['sometimes', 'nullable', DecimalString::percent()]];
    }

    public function thresholdPct(): string
    {
        return bcadd((string) ($this->validated('threshold_pct') ?? '10'), '0', 2);
    }
}
