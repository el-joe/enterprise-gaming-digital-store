<?php

namespace App\DataTables\Automation;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Enums\AiSuggestionType;
use App\Domains\Automation\Models\AiSuggestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * AI review queue (M15, `ai_suggestions`): drafts from the n8n marketing workflows wait for a person. The draft text is shown escaped and shortened.
 */
class AiSuggestionsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'ai-suggestions';
    }

    public static function permission(): string
    {
        return 'ai_suggestions.review';
    }

    public function columns(): array
    {
        return [
            new Column('created', __('admin.col.created'), 'ai_suggestions.created_at'),
            new Column('workflow', __('admin.col.workflow'), 'ai_suggestions.workflow_id'),
            new Column('type', __('admin.col.type'), 'ai_suggestions.type'),
            new Column('draft', __('admin.col.draft'), html: true),
            new Column('status', __('admin.col.status'), 'ai_suggestions.status', html: true),
            new Column('reviewer', __('admin.col.reviewer'), html: true),
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
                $query->where('ai_suggestions.status', $value);
            }, $this->enumOptions('suggestion_status', AiSuggestionStatus::cases())),
            new Filter('type', __('admin.col.type'), 'select', static function (Builder $query, string $value): void {
                $query->where('ai_suggestions.type', $value);
            }, $this->enumOptions('suggestion_type', AiSuggestionType::cases())),
        ];
    }

    protected function query(): Builder
    {
        return AiSuggestion::query()->select('ai_suggestions.*')->with('reviewedBy:id,name');
    }

    /**
     * @param  AiSuggestion  $row
     */
    protected function cells(Model $row): array
    {
        $api = url('/admin/api/ai-suggestions/'.$row->id);
        $pending = $row->status === AiSuggestionStatus::PendingReview;
        $draft = (string) json_encode($row->content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            'created' => $this->dateTime($row->created_at),
            'workflow' => (string) $row->workflow_id,
            'type' => Cell::label('suggestion_type', $row->type?->value),
            'draft' => '<span class="block max-w-md font-mono text-xs break-words text-content-secondary">'.e(Str::limit($draft, 280)).'</span>',
            'status' => Cell::status('suggestion_status', $row->status?->value),
            'reviewer' => $row->reviewedBy === null ? Cell::muted('—') : Cell::stack((string) $row->reviewedBy->name, $row->review_note),
            'actions' => ! $pending ? '' : Cell::actions(
                Cell::action(__('admin.common.approve'), $api.'/approve', confirm: __('admin.ai.approve_confirm')),
                Cell::action(__('admin.common.reject'), $api.'/reject', confirm: __('admin.ai.reject_confirm'), danger: true, reason: __('admin.common.note'), reasonField: 'note'),
            ),
        ];
    }
}
