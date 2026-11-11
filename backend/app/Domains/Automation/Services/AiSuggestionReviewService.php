<?php

namespace App\Domains\Automation\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;

/**
 * Human review of AI drafts from the n8n marketing workflows (M15, `ai_suggestions`, ADR-034
 * context). A decision is final: a draft is approved or rejected once, by a staff member with
 * `ai_suggestions.review`, with an optional note (required to reject). Approval publishes
 * nothing; staff copy what they accept into the right screen. Audited.
 */
class AiSuggestionReviewService
{
    public function __construct(private AuditLogger $audit) {}

    public function decide(AiSuggestion $suggestion, User $staff, AiSuggestionStatus $decision, ?string $note): AiSuggestion
    {
        if ($decision === AiSuggestionStatus::PendingReview) {
            throw new ApplicationException('Choose approve or reject.', 'validation_failed', 422);
        }

        return DB::transaction(function () use ($suggestion, $staff, $decision, $note): AiSuggestion {
            $locked = AiSuggestion::query()->whereKey($suggestion->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== AiSuggestionStatus::PendingReview) {
                throw new ApplicationException(__('admin.ai.already_decided'), 'conflict', 409);
            }

            $locked->forceFill([
                'status' => $decision,
                'reviewed_by' => $staff->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ])->save();

            $this->audit->record(new AuditEntry('ai_suggestions.'.$decision->value, $locked, ['status' => AiSuggestionStatus::PendingReview->value], ['status' => $decision->value], $note));

            return $locked;
        });
    }
}
