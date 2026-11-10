<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\DTOs\SearchSuggestion;
use App\Domains\Catalog\Services\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\SearchSuggestRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $search) {}

    public function suggest(SearchSuggestRequest $request): JsonResponse
    {
        $suggestions = $this->search->suggest(
            $request->validated('q'),
            $request->validated('locale', config('app.locale')),
            $request->boolean('commit'),
        );

        return $this->success(['suggestions' => array_map(static fn (SearchSuggestion $suggestion): array => [
            'type' => $suggestion->type,
            'title' => $suggestion->title,
            'slug' => $suggestion->slug,
        ], $suggestions)]);
    }

    public function popular(Request $request): JsonResponse
    {
        $locale = in_array($request->query('locale'), config('catalog.search_locales'), true)
            ? $request->query('locale')
            : config('app.locale');

        return $this->success(['terms' => $this->search->popular($locale)]);
    }
}
