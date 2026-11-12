<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A write-only secret: a new value, or null to clear it. A secret marked `upload` (the FCM
 * service-account JSON) may instead arrive as a `file` (multipart), read here and never stored as
 * a file. The secret's own rules and format apply in the service.
 */
class SetSecretRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['exclude_with:file', 'present', 'nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'max:20', 'mimetypes:application/json,text/plain'],
        ];
    }

    /**
     * The new secret (the uploaded file's content when one is sent), or null to clear.
     */
    public function secret(): ?string
    {
        $file = $this->file('file');
        $value = $file !== null ? (string) file_get_contents($file->getRealPath()) : $this->validated('value');

        return $value === null || trim((string) $value) === '' ? null : trim((string) $value);
    }
}
