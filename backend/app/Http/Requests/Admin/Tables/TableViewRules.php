<?php

namespace App\Http\Requests\Admin\Tables;

use Illuminate\Validation\Rule;

/**
 * Validation rules shared by saved filters and exports: the search text, filter values, sort
 * instructions and hidden columns of a table view.
 */
final class TableViewRules
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(string $prefix = ''): array
    {
        return [
            "{$prefix}search" => ['nullable', 'string', 'max:100'],
            "{$prefix}filters" => ['sometimes', 'array', 'max:20'],
            "{$prefix}filters.*" => ['nullable', 'string', 'max:64'],
            "{$prefix}order" => ['sometimes', 'array', 'max:3'],
            "{$prefix}order.*.column" => ['required', 'integer', 'min:0', 'max:50'],
            "{$prefix}order.*.dir" => ['required', 'string', Rule::in(['asc', 'desc'])],
            "{$prefix}hidden_columns" => ['sometimes', 'array', 'max:50'],
            "{$prefix}hidden_columns.*" => ['string', 'max:64'],
        ];
    }
}
