<?php

namespace App\Http\Requests\Admin;

use App\Domains\Shared\DTOs\ListQuery;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', 'max:64'],
            'filter' => ['sometimes', 'array'],
            'filter.*' => ['nullable', 'string', 'max:190'],
            'q' => ['sometimes', 'nullable', 'string', 'max:190'],
        ];
    }

    public function toListQuery(): ListQuery
    {
        $search = trim((string) $this->validated('q', ''));

        return new ListQuery(
            page: (int) $this->validated('page', 1),
            perPage: (int) $this->validated('per_page', 20),
            sort: $this->validated('sort'),
            filters: $this->validated('filter', []),
            search: $search === '' ? null : $search,
        );
    }
}
