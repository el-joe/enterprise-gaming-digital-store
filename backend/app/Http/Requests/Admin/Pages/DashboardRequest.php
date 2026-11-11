<?php

namespace App\Http\Requests\Admin\Pages;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'days' => ['sometimes', 'integer', Rule::in((array) config('admin.dashboard.ranges'))],
        ];
    }

    public function days(): int
    {
        return (int) ($this->validated('days') ?? config('admin.dashboard.default_range'));
    }
}
