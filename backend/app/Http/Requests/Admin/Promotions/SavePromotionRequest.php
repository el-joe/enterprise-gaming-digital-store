<?php

namespace App\Http\Requests\Admin\Promotions;

use App\Domains\Promotions\Enums\PromotionTargetTargetType;
use App\Domains\Promotions\Enums\PromotionType;
use App\Domains\Shared\Rules\DecimalString;
use App\Http\Requests\Admin\AdminSaveRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SavePromotionRequest extends AdminSaveRequest
{
    private const array TARGET_TABLES = [
        'product' => 'products',
        'variant' => 'product_variants',
        'category' => 'categories',
        'platform' => 'gaming_platforms',
    ];

    /**
     * `targets` replaces the promotion's targets when present.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [$this->presence(), 'string', 'max:120'],
            'campaign' => ['sometimes', 'nullable', 'string', 'max:120'],
            'type' => [$this->presence(), Rule::enum(PromotionType::class)],
            'value' => [$this->presence(), DecimalString::positiveMoney(), $this->percentAtMostHundred(...)],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'stackable' => ['sometimes', 'boolean'],
            'badge_text' => ['sometimes', 'nullable', 'string', 'max:40'],
            'starts_at' => [$this->presence(), 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'is_active' => [$this->presence(), 'boolean'],
            'targets' => ['sometimes', 'array', 'max:500'],
            'targets.*.type' => ['required', Rule::enum(PromotionTargetTargetType::class)],
            'targets.*.id' => ['required', 'integer', $this->targetExists(...)],
        ];
    }

    private function targetExists(string $attribute, mixed $value, Closure $fail): void
    {
        $type = $this->input(str_replace('.id', '.type', $attribute));
        $table = self::TARGET_TABLES[(string) $type] ?? null;

        if ($table !== null && ! DB::table($table)->where('id', $value)->exists()) {
            $fail("The selected {$attribute} does not exist.");
        }
    }

    private function percentAtMostHundred(string $attribute, mixed $value, Closure $fail): void
    {
        $type = $this->input('type') ?? $this->route('promotion')?->type?->value;

        if ($type === PromotionType::Percent->value && is_string($value) && bccomp($value, '100', 6) === 1) {
            $fail('A percentage promotion cannot exceed 100.');
        }
    }
}
