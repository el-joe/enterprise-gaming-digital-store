<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Enums\AiSuggestionType;
use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Catalog\Models\Product;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * POST /suggestions: AI and pricing output from n8n lands in ai_suggestions as `pending_review`
 * and nothing else happens. Publication is a human act in the admin (approval creates a draft,
 * PROJECT_ARCHITECTURE and DATABASE_DESIGN section 14). A retried request with the same content
 * returns the existing suggestion.
 */
class AiSuggestionService
{
    /**
     * Section C workflows and B10 may only create the suggestion types listed for them.
     */
    private const array TYPES_BY_WORKFLOW = [
        'B10' => [AiSuggestionType::Pricing],
        'C01' => [AiSuggestionType::Product],
        'C02' => [AiSuggestionType::Product],
        'C03' => [AiSuggestionType::Product],
        'C04' => [AiSuggestionType::Campaign],
        'C05' => [AiSuggestionType::Bundle],
        'C06' => [AiSuggestionType::Coupon],
        'C07' => [AiSuggestionType::BlogIdea],
        'C08' => [AiSuggestionType::SeoDescription],
        'C09' => [AiSuggestionType::MarketingCopy],
        'C10' => [AiSuggestionType::SocialPost],
    ];

    /**
     * @param  array<string, mixed>  $content
     * @return array{id: int, status: string, type: string, created: bool}
     */
    public function create(AutomationCaller $caller, AiSuggestionType $type, ?string $productId, array $content): array
    {
        if (! in_array($type, self::TYPES_BY_WORKFLOW[$caller->workflowId] ?? [], true)) {
            throw new ApplicationException("Workflow {$caller->workflowId} may not create {$type->value} suggestions.", 'forbidden', 403);
        }

        $subjectId = null;

        if ($productId !== null) {
            $subjectId = Product::query()->where('public_id', $productId)->value('id');

            if ($subjectId === null) {
                throw new ApplicationException('No product has this id.', 'validation_failed', 422, ['product_id' => ['No product has this id.']]);
            }
        }

        $hash = hash('sha256', implode('|', [$caller->workflowId, $type->value, (string) $subjectId, json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]));
        $existing = AiSuggestion::query()->where('dedupe_hash', $hash)->first();

        if ($existing !== null) {
            return $this->present($existing, false);
        }

        $suggestion = new AiSuggestion;
        $suggestion->forceFill([
            'workflow_id' => $caller->workflowId,
            'type' => $type,
            'subject_type' => $subjectId === null ? null : (new Product)->getMorphClass(),
            'subject_id' => $subjectId,
            'content' => [...$content, 'generated_by' => 'n8n', 'execution_key' => $caller->executionKey],
            'dedupe_hash' => $hash,
            'status' => AiSuggestionStatus::PendingReview,
        ]);

        try {
            $suggestion->save();
        } catch (UniqueConstraintViolationException) {
            return $this->present(AiSuggestion::query()->where('dedupe_hash', $hash)->firstOrFail(), false);
        }

        return $this->present($suggestion, true);
    }

    /**
     * @return array{id: int, status: string, type: string, created: bool}
     */
    private function present(AiSuggestion $suggestion, bool $created): array
    {
        return ['id' => $suggestion->id, 'status' => $suggestion->status->value, 'type' => $suggestion->type->value, 'created' => $created];
    }
}
