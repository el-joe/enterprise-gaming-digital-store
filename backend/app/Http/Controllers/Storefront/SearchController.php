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
            $request->validated('locale', $this->requestLocale()),
            $request->boolean('commit'),
        );

        return $this->success(['suggestions' => array_map(static fn (SearchSuggestion $suggestion): array => [
            'type' => $suggestion->type,
            'title' => $suggestion->title,
            'slug' => $suggestion->slug,
            'image' => $suggestion->image,
        ], $suggestions)]);
    }

    /**
     * Without an explicit `locale`, the request locale (Accept-Language, M-A3) when it is a search
     * locale, else the default.
     */
    private function requestLocale(): string
    {
        return in_array(app()->getLocale(), config('catalog.search_locales'), true) ? app()->getLocale() : (string) config('app.fallback_locale');
    }

    public function popular(Request $request): JsonResponse
    {
        $locale = in_array($request->query('locale'), config('catalog.search_locales'), true)
            ? $request->query('locale')
            : $this->requestLocale();

        return $this->success(['terms' => $this->search->popular($locale)]);
    }
}
