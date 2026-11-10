<?php

namespace App\Http\Requests\Automation;

use App\Domains\Automation\Enums\AiSuggestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /suggestions. `content` is the draft (title, body, rationale, and type-specific fields), at
 * most 20 KB as JSON. It is stored for human review only.
 */
class StoreSuggestionRequest extends FormRequest
{
    public const int MAX_CONTENT_BYTES = 20480;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AiSuggestionType::class)],
            'product_id' => ['sometimes', 'nullable', 'string', 'ulid'],
            'content' => ['required', 'array', 'min:1', 'max:50', function (string $attribute, mixed $value, \Closure $fail): void {
                if (strlen((string) json_encode($value)) > self::MAX_CONTENT_BYTES) {
                    $fail('The content may not be larger than '.self::MAX_CONTENT_BYTES.' bytes.');
                }
            }],
            'content.title' => ['required', 'string', 'max:255'],
        ];
    }
}
