<?php

namespace App\Http\Controllers\Admin\Automation;

use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Automation\Services\AiSuggestionReviewService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Mcp\DecideMcpActionRequest;
use Illuminate\Http\JsonResponse;

/**
 * AI draft review (M15, `ai_suggestions.review`): approve with an optional note, reject with a
 * required note (same rule as MCP action decisions).
 */
class AiSuggestionController extends Controller
{
    public function __construct(private AiSuggestionReviewService $reviews) {}

    public function approve(DecideMcpActionRequest $request, AiSuggestion $suggestion): JsonResponse
    {
        $this->reviews->decide($suggestion, $request->user(), AiSuggestionStatus::Approved, $request->validated('note'));

        return $this->success(message: 'Approved. Nothing is published automatically.');
    }

    public function reject(DecideMcpActionRequest $request, AiSuggestion $suggestion): JsonResponse
    {
        $this->reviews->decide($suggestion, $request->user(), AiSuggestionStatus::Rejected, (string) $request->validated('note'));

        return $this->success(message: 'Rejected.');
    }
}
