<?php

namespace App\Http\Requests\Web\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Filters of the digital codes page (M13): a state and a search on product name or order number.
 */
class CodeListPageRequest extends AccountPageRequest
{
    /** @var list<string> */
    public const array STATES = ['sealed', 'revealed', 'refunded'];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'state' => ['sometimes', 'nullable', Rule::in(self::STATES)],
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    public function state(): ?string
    {
        return $this->validated('state');
    }

    public function search(): ?string
    {
        $search = trim((string) $this->validated('q'));

        return $search === '' ? null : $search;
    }
}
