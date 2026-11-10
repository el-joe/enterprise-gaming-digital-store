<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Enums\AiSuggestionType;
use App\Domains\Automation\Services\AiSuggestionService;
use App\Domains\Automation\Services\MarketingDataService;
use App\Http\Requests\Automation\AutomationQueryRequest;
use App\Http\Requests\Automation\StoreSuggestionRequest;
use Illuminate\Http\JsonResponse;

/**
 * Aggregated inputs for the AI marketing workflows and the review queue they write to. Nothing
 * is published: suggestions are stored as `pending_review`.
 */
class MarketingController extends AutomationController
{
    public function __construct(private MarketingDataService $data, private AiSuggestionService $suggestions) {}

    public function trending(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->data->trending($request->intOr('days', 7), $request->intOr('limit', 20)));
    }

    public function productContext(string $productId): JsonResponse
    {
        return $this->success($this->data->productContext($productId));
    }

    public function missingDescriptions(AutomationQueryRequest $request): JsonResponse
    {
        return $this->success($this->data->productsMissingDescriptions($request->intOr('limit', 10)));
    }

    public function storeSuggestion(StoreSuggestionRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $content */
        $content = $request->validated('content');
        $result = $this->suggestions->create($this->caller($request), AiSuggestionType::from((string) $request->validated('type')), $request->validated('product_id'), $content);

        return $this->success($result, $result['created'] ? 'Suggestion queued for review.' : 'Suggestion already queued.', status: $result['created'] ? 201 : 200);
    }
}
