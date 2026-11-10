<?php

namespace App\Domains\Cms\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\HtmlSanitizer;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;

/**
 * @extends CrudService<BlogPost>
 */
class BlogPostService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private HtmlSanitizer $sanitizer)
    {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return BlogPost::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.blog_post';
    }

    protected function filters(): array
    {
        return ['status' => 'exact', 'blog_category_id' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'title', 'published_at', 'created_at'];
    }

    protected function searchColumns(): array
    {
        return ['title', 'slug'];
    }

    protected function relations(): array
    {
        return ['blogCategory:id,name,slug'];
    }

    protected function auditIgnored(): array
    {
        return ['body'];
    }

    /**
     * @param  BlogPost  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        if (array_key_exists('body', $attributes)) {
            $attributes['body'] = $this->sanitizer->sanitize($attributes['body']);
        }

        if (! $model->exists) {
            $attributes['author_id'] = Auth::id();
        }

        return $attributes;
    }

    /**
     * @param  BlogPost  $model
     */
    protected function applyGuarded(Model $model, array $data): void
    {
        $status = isset($data['status']) ? BlogPostStatus::from($data['status']) : ($model->status ?? BlogPostStatus::Draft);
        $publishedAt = array_key_exists('published_at', $data) ? $data['published_at'] : $model->published_at;

        if ($status === BlogPostStatus::Scheduled && ($publishedAt === null || Date::parse($publishedAt)->isPast())) {
            throw ValidationException::withMessages(['published_at' => ['A scheduled post needs a publication time in the future.']]);
        }

        $model->forceFill([
            'status' => $status,
            'published_at' => match ($status) {
                BlogPostStatus::Published => $publishedAt ?? now(),
                BlogPostStatus::Scheduled => $publishedAt,
                BlogPostStatus::Draft => null,
            },
        ]);
    }
}
