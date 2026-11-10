<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Shared\Rules\Slug;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveBlogPostRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'blog_category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('blog_categories', 'id')],
            'title' => [$this->presence(), 'string', 'max:190'],
            'slug' => [$this->presence(), 'string', 'max:210', new Slug, Rule::unique('blog_posts', 'slug')->ignore($this->route('blogPost'))],
            'excerpt' => ['sometimes', 'nullable', 'string', 'max:500'],
            'body' => [$this->presence(), 'string'],
            'cover_media_id' => ['sometimes', 'nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'status' => ['sometimes', Rule::enum(BlogPostStatus::class)],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
