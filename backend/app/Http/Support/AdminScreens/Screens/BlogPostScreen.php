<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Cms\Models\BlogPost;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Blog posts (spec Phase 9.9): draft, scheduled or published.
 */
class BlogPostScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'blog-posts';
    }

    public static function viewPermission(): string
    {
        return 'content.blog';
    }

    public static function managePermission(): string
    {
        return 'content.blog';
    }

    public static function model(): string
    {
        return BlogPost::class;
    }

    public static function table(): string
    {
        return 'blog-posts';
    }

    protected function apiPath(): string
    {
        return '/admin/api/blog-posts';
    }

    public function translations(): ?array
    {
        return ['type' => 'blog_post', 'fields' => (array) config('admin.translations.blog_post.fields')];
    }

    public function seoType(): ?string
    {
        return 'blog_post';
    }

    public function fields(?Model $record): array
    {
        return [
            'post' => [
                Field::text('title', $this->l('title'))->required()->max(190),
                Field::text('slug', $this->l('slug'))->required()->max(210)->ltr()->help(__('admin.help.slug')),
                Field::select('blog_category_id', $this->l('blog_category_id'), Options::blogCategories())->nullable(),
                Field::select('status', $this->l('status'), Options::enum('blog_status', BlogPostStatus::cases()))->required(),
                Field::datetime('published_at', $this->l('published_at')),
                Field::image('cover_media_id', $this->l('cover_media_id'))->nullable()->help(__('admin.help.image_upload')),
                Field::textarea('excerpt', $this->l('excerpt'))->nullable()->max(500),
                Field::html('body', $this->l('body'))->required()->help(__('admin.help.html')),
            ],
        ];
    }
}
