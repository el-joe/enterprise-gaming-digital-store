<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Arabic (or any non-base supported locale) text of one record (M15). The route names the type
 * (`admin.api.translations.{type}`), so the allowed fields come from config, never from input.
 */
class SaveTranslationsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $fields = (array) config('admin.translations.'.$this->type().'.fields', []);
        $locales = array_values(array_diff((array) config('app.supported_locales'), [config('app.fallback_locale')]));

        $rules = [
            'locale' => ['required', 'string', Rule::in($locales)],
            'values' => ['required', 'array:'.implode(',', array_keys($fields))],
        ];

        foreach ($fields as $field => $kind) {
            $rules["values.{$field}"] = ['sometimes', 'nullable', 'string', 'max:'.($kind === 'text' ? 500 : 60000)];
        }

        return $rules;
    }

    public function type(): string
    {
        return (string) $this->route()?->defaults['translation_type'];
    }
}
