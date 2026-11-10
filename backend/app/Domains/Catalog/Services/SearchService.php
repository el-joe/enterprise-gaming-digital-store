<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\SearchSuggestion;
use App\Domains\Catalog\Models\Product;
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

    private const int RESULTS_LIMIT = 48;

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

    /**
     * The search results page (M12): matching documents found the way suggestions find them,
     * split into product ids (in rank order) and the other suggestions (platforms, categories).
     * The submitted term is counted for popular searches.
     *
     * @return array{product_ids: list<int>, suggestions: list<SearchSuggestion>}
     */
    public function results(string $term, string $locale): array
    {
        $normalized = $this->normalize($term);

        $documents = $this->search->suggest($normalized, $locale, self::RESULTS_LIMIT)
            ->filter(static fn (SearchDocument $document): bool => $document->searchable !== null);

        $productType = (new Product)->getMorphClass();

        $productIds = $documents->where('searchable_type', $productType)->map(static fn (SearchDocument $document): int => (int) $document->searchable_id)->unique()->values()->all();

        $suggestions = $documents->reject(static fn (SearchDocument $document): bool => $document->searchable_type === $productType)
            ->map(static fn (SearchDocument $document): SearchSuggestion => new SearchSuggestion($document->searchable_type, $document->title, (string) $document->searchable->slug))
            ->values()
            ->all();

        $this->search->record($normalized, $locale, count($productIds) + count($suggestions));
        Cache::forget($this->popularKey($locale));

        return ['product_ids' => $productIds, 'suggestions' => $suggestions];
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
