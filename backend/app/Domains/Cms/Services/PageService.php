<?php

namespace App\Domains\Cms\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Cms\Models\Page;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\HtmlSanitizer;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @extends CrudService<Page>
 */
class PageService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private HtmlSanitizer $sanitizer)
    {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return Page::class;
    }

    protected function auditPrefix(): string
    {
        return 'cms.page';
    }

    protected function filters(): array
    {
        return ['status' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'title', 'published_at', 'created_at'];
    }

    protected function searchColumns(): array
    {
        return ['title', 'slug'];
    }

    protected function auditIgnored(): array
    {
        return ['body'];
    }

    /**
     * @param  Page  $model
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
     * @param  Page  $model
     */
    protected function applyGuarded(Model $model, array $data): void
    {
        $status = isset($data['status']) ? PageStatus::from($data['status']) : ($model->status ?? PageStatus::Draft);

        $model->forceFill([
            'status' => $status,
            'published_at' => $status === PageStatus::Published ? ($model->published_at ?? now()) : null,
        ]);
    }
}
