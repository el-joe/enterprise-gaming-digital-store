<?php

namespace App\Http\Requests\Admin\Tables;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A DataTables server-side request (M14). Only paging, the search text, up to three sort
 * instructions by column position and the filter values are read; the table class decides
 * what they mean. Column definitions sent by the client are ignored.
 */
class AdminTableRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'draw' => ['required', 'integer', 'min:0'],
            'start' => ['required', 'integer', 'min:0', 'max:1000000'],
            'length' => ['required', 'integer', Rule::in((array) config('admin.tables.page_lengths'))],
            'search' => ['sometimes', 'array'],
            'search.value' => ['nullable', 'string', 'max:100'],
            'order' => ['sometimes', 'array', 'max:3'],
            'order.*.column' => ['required', 'integer', 'min:0', 'max:50'],
            'order.*.dir' => ['required', 'string', Rule::in(['asc', 'desc'])],
            'filters' => ['sometimes', 'array', 'max:20'],
            'filters.*' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array{draw: int, start: int, length: int, search: string, order: list<array{column: int, dir: string}>, filters: array<string, mixed>}
     */
    public function tableInput(): array
    {
        return [
            'draw' => (int) $this->validated('draw'),
            'start' => (int) $this->validated('start'),
            'length' => (int) $this->validated('length'),
            'search' => (string) ($this->validated('search.value') ?? ''),
            'order' => array_values(array_map(static fn (array $item): array => ['column' => (int) $item['column'], 'dir' => (string) $item['dir']], (array) $this->validated('order', []))),
            'filters' => (array) $this->validated('filters', []),
        ];
    }
}
