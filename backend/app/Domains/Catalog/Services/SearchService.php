<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\SearchSuggestion;
use App\Domains\Catalog\Models\SearchDocument;
use App\Domains\Catalog\Repositories\SearchRepository;
use Illuminate\Support\Facades\Cache;

/**
 * Instant search across games, platforms, products and SKUs.
 */
class SearchService
{
    private const int SUGGESTION_LIMIT = 10;

    private const int POPULAR_LIMIT = 10;

    private const int POPULAR_CACHE_SECONDS = 600;

    public function __construct(private SearchRepository $search) {}

    /**
     * @return list<SearchSuggestion>
     */
    public function suggest(string $term, string $locale, bool $record = false): array
    {
        $normalized = $this->normalize($term);

        $suggestions = $this->search->suggest($normalized, $locale, self::SUGGESTION_LIMIT)
            ->filter(static fn (SearchDocument $document): bool => $document->searchable !== null)
            ->map(static fn (SearchDocument $document): SearchSuggestion => new SearchSuggestion(
                type: $document->searchable_type,
                title: $document->title,
                slug: (string) $document->searchable->slug,
            ))
            ->values()
            ->all();

        if ($record) {
            $this->search->record($normalized, $locale, count($suggestions));
            Cache::forget($this->popularKey($locale));
        }

        return $suggestions;
    }

    /**
     * @return list<string>
     */
    public function popular(string $locale): array
    {
        return Cache::remember(
            $this->popularKey($locale),
            self::POPULAR_CACHE_SECONDS,
            fn (): array => $this->search->popular($locale, self::POPULAR_LIMIT),
        );
    }

    private function normalize(string $term): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $term) ?? ''));
    }

    private function popularKey(string $locale): string
    {
        return "search:popular:{$locale}";
    }
}
