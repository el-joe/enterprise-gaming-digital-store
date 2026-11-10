<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The search page term. Too short or too long a term shows the empty search page.
 */
class SearchPageRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['q' => ['sometimes', 'nullable', 'string', 'max:100']];
    }

    protected function getRedirectUrl(): string
    {
        return $this->url();
    }
}
