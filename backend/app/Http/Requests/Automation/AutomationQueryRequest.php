<?php

namespace App\Http\Requests\Automation;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query parameters shared by the automation report and monitor endpoints. A window is either a
 * named `period` (UTC) or explicit `from` and `to` dates (whole days, at most 366). Authorization is
 * the token, signature and scope middleware on the route.
 */
class AutomationQueryRequest extends FormRequest
{
    public const array PERIODS = ['today', 'yesterday', 'last_15_minutes', 'last_hour', 'last_24_hours', 'last_7_days', 'last_week', 'last_30_days', 'last_month'];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'period' => ['sometimes', 'nullable', Rule::in(self::PERIODS)],
            'from' => ['nullable', 'date', 'required_with:to'],
            'to' => ['nullable', 'date', 'required_with:from', 'after_or_equal:from'],
            'since' => ['sometimes', 'nullable', 'date'],
            'older_than' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10080'],
            'window' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10080'],
            'days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:90'],
            'limit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'threshold_pct' => ['sometimes', 'nullable', 'numeric', 'min:-100', 'max:100'],
            'level' => ['sometimes', 'nullable', Rule::in(['warning', 'critical'])],
            'by' => ['sometimes', 'nullable', Rule::in(['game', 'platform'])],
            'detail' => ['sometimes', 'nullable', Rule::in(['0', '1'])],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $from = $this->input('from');
            $to = $this->input('to');

            if (is_string($from) && is_string($to) && strtotime($from) !== false && strtotime($to) !== false
                && CarbonImmutable::parse($from)->startOfDay()->diffInDays(CarbonImmutable::parse($to)->startOfDay()) > 366) {
                $validator->errors()->add('to', 'The date range cannot exceed 366 days.');
            }
        }];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function window(string $defaultPeriod): array
    {
        if ($this->validated('from') !== null && $this->validated('to') !== null) {
            return [
                CarbonImmutable::parse((string) $this->validated('from'))->utc()->startOfDay(),
                CarbonImmutable::parse((string) $this->validated('to'))->utc()->endOfDay(),
            ];
        }

        $now = CarbonImmutable::now('UTC');

        return match ((string) ($this->validated('period') ?? $defaultPeriod)) {
            'today' => [$now->startOfDay(), $now],
            'yesterday' => [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()],
            'last_15_minutes' => [$now->subMinutes(15), $now],
            'last_hour' => [$now->subHour(), $now],
            'last_24_hours' => [$now->subDay(), $now],
            'last_7_days' => [$now->subDays(7), $now],
            'last_week' => [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
            'last_30_days' => [$now->subDays(30), $now],
            'last_month' => [$now->subMonthNoOverflow()->startOfMonth(), $now->subMonthNoOverflow()->endOfMonth()],
        };
    }

    public function since(int $defaultMinutes): CarbonImmutable
    {
        $since = $this->validated('since');

        return $since === null ? CarbonImmutable::now('UTC')->subMinutes($defaultMinutes) : CarbonImmutable::parse((string) $since)->utc();
    }

    public function intOr(string $key, int $default): int
    {
        $value = $this->validated($key);

        return $value === null ? $default : (int) $value;
    }

    public function thresholdPct(string $default): string
    {
        $value = $this->validated('threshold_pct');

        return $value === null ? $default : (string) $value;
    }
}
