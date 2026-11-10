<?php

namespace App\Domains\Catalog\Repositories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\SearchDocument;
use App\Domains\Catalog\Models\SearchQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SearchRepository
{
    private const int MIN_FULLTEXT_TOKEN = 3;

    /** InnoDB's default stopwords, which match nothing and would void a required (+) term. */
    private const array STOPWORDS = ['a', 'about', 'an', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en', 'for', 'from', 'how', 'i', 'in', 'is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'und', 'www'];

    /**
     * Find documents by title prefix, fulltext words or product and variant SKU prefix. SKUs get
     * their own path because the fulltext parser splits them on hyphens and drops short parts.
     *
     * @return Collection<int, SearchDocument>
     */
    public function suggest(string $term, string $locale, int $limit): Collection
    {
        $booleanQuery = $this->booleanQuery($term);
        $prefix = addcslashes($term, '%_\\').'%';

        $query = SearchDocument::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->where(function (Builder $inner) use ($booleanQuery, $prefix): void {
                $inner->where('title', 'like', $prefix)
                    ->orWhere(function (Builder $sku) use ($prefix): void {
                        $sku->where('searchable_type', (new Product)->getMorphClass())
                            ->where(function (Builder $ids) use ($prefix): void {
                                $ids->whereIn('searchable_id', Product::query()->select('id')->where('sku', 'like', $prefix))
                                    ->orWhereIn('searchable_id', ProductVariant::query()->select('product_id')->where('sku', 'like', $prefix));
                            });
                    });

                if ($booleanQuery !== null) {
                    $inner->orWhereRaw('MATCH(title, keywords) AGAINST (? IN BOOLEAN MODE)', [$booleanQuery]);
                }
            })
            ->with('searchable');

        if ($booleanQuery !== null) {
            $query->orderByRaw('MATCH(title, keywords) AGAINST (? IN BOOLEAN MODE) DESC', [$booleanQuery]);
        }

        return $query->orderByDesc('boost')->orderBy('title')->limit($limit)->get();
    }

    /**
     * Count a search atomically: hits increase even when two requests race on the same new term.
     */
    public function record(string $term, string $locale, int $resultsCount): void
    {
        SearchQuery::query()->upsert(
            [['term' => $term, 'locale' => $locale, 'hits' => 1, 'results_count' => $resultsCount, 'last_searched_at' => now(), 'created_at' => now(), 'updated_at' => now()]],
            ['term', 'locale'],
            ['hits' => DB::raw('hits + 1'), 'results_count' => $resultsCount, 'last_searched_at' => now(), 'updated_at' => now()],
        );
    }

    /**
     * @return list<string>
     */
    public function popular(string $locale, int $limit): array
    {
        return SearchQuery::query()
            ->where('locale', $locale)
            ->where('results_count', '>', 0)
            ->orderByDesc('hits')
            ->limit($limit)
            ->pluck('term')
            ->all();
    }

    private function booleanQuery(string $term): ?string
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $term, flags: PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = array_filter($tokens, static fn (string $token): bool => mb_strlen($token) >= self::MIN_FULLTEXT_TOKEN
            && ! in_array(mb_strtolower($token), self::STOPWORDS, true));

        return $tokens === [] ? null : implode(' ', array_map(static fn (string $token): string => "+{$token}*", $tokens));
    }
}
