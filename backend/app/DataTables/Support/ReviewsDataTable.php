<?php

namespace App\DataTables\Support;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Review moderation (spec Phase 9 reviews, Figma "Admin / Reviews / Moderation Queue"): approve, reject with a reason, feature on the homepage.
 */
class ReviewsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'reviews';
    }

    public static function permission(): string
    {
        return 'catalog.manage';
    }

    public function columns(): array
    {
        return [
            new Column('product', __('admin.col.product'), html: true),
            new Column('customer', __('admin.col.customer'), html: true),
            new Column('rating', __('admin.col.rating'), 'reviews.rating', numeric: true),
            new Column('review', __('admin.col.review'), html: true),
            new Column('status', __('admin.col.status'), 'reviews.status', html: true),
            new Column('featured', __('admin.col.featured'), 'reviews.is_featured', html: true),
            new Column('created', __('admin.col.created'), 'reviews.created_at'),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    public function filters(): array
    {
        return [
            new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('reviews.status', $value);
            }, $this->enumOptions('review_status', ReviewStatus::cases())),
            new Filter('rating', __('admin.col.rating'), 'select', static function (Builder $query, string $value): void {
                $query->where('reviews.rating', (int) $value);
            }, ['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1']),
        ];
    }

    protected function query(): Builder
    {
        return Review::query()->select('reviews.*')->with(['product:id,name', 'user:id,name']);
    }

    /**
     * @param  Review  $row
     */
    protected function cells(Model $row): array
    {
        $api = url('/admin/api/reviews/'.$row->id);
        $status = $row->status?->value;

        return [
            'product' => e((string) ($row->product?->name ?? '—')),
            'customer' => e((string) ($row->user?->name ?? '—')),
            'rating' => str_repeat('★', (int) $row->rating),
            'review' => Cell::stack((string) ($row->title ?? '—'), Str::limit((string) $row->body, 140)),
            'status' => Cell::status('review_status', $status),
            'featured' => Cell::yesNo((bool) $row->is_featured),
            'created' => $this->dateTime($row->created_at),
            'actions' => Cell::actions(...array_filter([
                $status !== 'approved' ? Cell::action(__('admin.common.approve'), $api.'/approve', confirm: __('admin.reviews.approve_confirm')) : null,
                $status !== 'rejected' ? Cell::action(__('admin.common.reject'), $api.'/reject', confirm: __('admin.reviews.reject_confirm'), danger: true, reason: __('admin.common.reason')) : null,
                $status === 'approved' ? Cell::action($row->is_featured ? __('admin.reviews.unfeature') : __('admin.reviews.feature'), $api.'/feature', 'PATCH', ['is_featured' => ! $row->is_featured]) : null,
            ])),
        ];
    }
}
