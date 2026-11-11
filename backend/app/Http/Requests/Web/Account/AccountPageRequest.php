<?php

namespace App\Http\Requests\Web\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Query of a paginated account page (M13). An invalid query sends the customer back to the page
 * without it, as the storefront listings do.
 */
class AccountPageRequest extends FormRequest
{
    public const int PER_PAGE = 20;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function page(): int
    {
        return (int) ($this->validated('page') ?? 1);
    }

    protected function getRedirectUrl(): string
    {
        return $this->url();
    }
}
