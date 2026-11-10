<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Shared\Rules\Slug;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveGamingPlatformRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [$this->presence(), 'string', 'max:120'],
            'slug' => [$this->presence(), 'string', 'max:140', new Slug, Rule::unique('gaming_platforms', 'slug')->ignore($this->route('gamingPlatform'))],
            'description' => ['sometimes', 'nullable', 'string'],
            'logo_media_id' => ['sometimes', 'nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'banner_media_id' => ['sometimes', 'nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'popularity_score' => ['sometimes', 'integer', 'min:0'],
            'is_active' => [$this->presence(), 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
