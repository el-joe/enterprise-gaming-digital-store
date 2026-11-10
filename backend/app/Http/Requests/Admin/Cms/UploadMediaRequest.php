<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadMediaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request. SVG is not on the list.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $limit = (int) config('catalog.media.max_dimension_pixels');

        return [
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,avif',
                'mimetypes:image/jpeg,image/png,image/webp,image/avif',
                'max:'.config('catalog.media.max_upload_kilobytes'),
                Rule::dimensions()->maxWidth($limit)->maxHeight($limit),
            ],
            'alt' => ['sometimes', 'nullable', 'string', 'max:190'],
            'folder' => ['sometimes', 'nullable', 'string', 'max:190'],
            'license_note' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
