<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Catalog\Rules\NotReservedSlug;
use App\Domains\Shared\Rules\Slug;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveCategoryRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => [$this->presence(), 'string', 'max:120'],
            'slug' => [$this->presence(), 'string', 'max:140', new Slug, new NotReservedSlug, Rule::unique('categories', 'slug')->ignore($this->route('category'))],
            'description' => ['sometimes', 'nullable', 'string'],
            'banner_media_id' => ['sometimes', 'nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'is_active' => [$this->presence(), 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
