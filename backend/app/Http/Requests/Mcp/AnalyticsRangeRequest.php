<?php

namespace App\Http\Requests\Mcp;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Validation\Rule;

/**
 * A date window for analytics and operations tools. `from` and `to` are whole days, inclusive,
 * in UTC; the window is at most 366 days.
 */
class AnalyticsRangeRequest extends McpPageRequest
{
    public const int MAX_DAYS = 366;

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from', $this->withinMaxRange(...)],
            'group_by' => ['sometimes', 'nullable', Rule::in(['day', 'week', 'month', 'none'])],
            'limit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'code' => ['sometimes', 'nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    public function from(): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $this->validated('from'))->utc()->startOfDay();
    }

    public function to(): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $this->validated('to'))->utc()->endOfDay();
    }

    public function groupBy(): string
    {
        return (string) ($this->validated('group_by') ?? 'day');
    }

    public function limit(): int
    {
        return (int) ($this->validated('limit') ?? 10);
    }

    private function withinMaxRange(string $attribute, mixed $value, Closure $fail): void
    {
        $from = $this->input('from');

        if (! is_string($from) || ! is_string($value) || strtotime($from) === false || strtotime($value) === false) {
            return;
        }

        if (CarbonImmutable::parse($from)->startOfDay()->diffInDays(CarbonImmutable::parse($value)->startOfDay()) > self::MAX_DAYS) {
            $fail('The date range cannot exceed '.self::MAX_DAYS.' days.');
        }
    }
}
