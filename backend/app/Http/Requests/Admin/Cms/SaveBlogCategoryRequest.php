<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Shared\Rules\Slug;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveBlogCategoryRequest extends AdminSaveRequest
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
            'slug' => [$this->presence(), 'string', 'max:140', new Slug, Rule::unique('blog_categories', 'slug')->ignore($this->route('blogCategory'))],
        ];
    }
}
