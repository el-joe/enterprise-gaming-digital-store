<?php

namespace App\Http\Requests\Admin\Seo;

use App\Domains\Shared\Rules\SafeUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSeoMetadataRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::in((array) config('app.supported_locales'))],
            'title' => ['sometimes', 'nullable', 'string', 'max:190'],
            'description' => ['sometimes', 'nullable', 'string', 'max:320'],
            'canonical_override' => ['sometimes', 'nullable', 'string', 'max:500', new SafeUrl],
            'robots' => ['sometimes', 'nullable', 'string', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'og_image_media_id' => ['sometimes', 'nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
        ];
    }
}
