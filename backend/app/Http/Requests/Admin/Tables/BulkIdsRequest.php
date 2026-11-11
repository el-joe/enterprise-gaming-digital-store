<?php

namespace App\Http\Requests\Admin\Tables;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Rows selected for a bulk action (at most one page of 100), with an optional note.
 */
class BulkIdsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'string', 'max:64', 'distinct'],
            'note' => [$this->routeIs('admin.api.tables.reconciliation.bulk.*') ? 'required' : 'sometimes', 'nullable', 'string', 'min:5', 'max:1000'],
        ];
    }
}
