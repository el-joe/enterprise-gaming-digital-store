<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\SearchSuggestion;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\SearchService;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SearchPageRequest;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Support\StorefrontRequest;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\View\View;

/**
 * Search results (`/search?q=`, `noindex`): products matching the term the way suggestions
 * match it (title prefix, full text, SKU prefix), plus matching platforms and categories.
 * Submitting counts the term for popular searches, as `commit=1` does on the JSON.
 */
class SearchController extends Controller
{
    public function show(SearchPageRequest $request, SearchService $search, StorefrontCatalogService $catalog, ContentTranslator $translator, StorefrontRequest $storefront): View
    {
        $term = trim((string) $request->validated('q', ''));
        $locale = in_array(app()->getLocale(), (array) config('catalog.search_locales'), true) ? app()->getLocale() : (string) config('app.fallback_locale');

        $products = [];
        $links = [];

        if (mb_strlen($term) >= 2) {
            $results = $search->results($term, $locale);
            $listed = $catalog->listedProducts($results['product_ids']);
            $ordered = collect($results['product_ids'])->map(static fn (int $id): ?Product => $listed->get($id))->filter()->values();
            $cards = $catalog->cards(new EloquentCollection($ordered->all()), $storefront->currency($request));

            $models = $cards->map(static fn (ProductCard $card): Product => $card->product);
            $translator->preload([...$models, ...$models->map->gamingPlatform, ...$models->map->primaryCategory], ['name', 'short_description']);

            $products = ProductCardResource::collection($cards)->resolve($request);
            $links = array_values(array_filter($results['suggestions'], static fn (SearchSuggestion $suggestion): bool => $suggestion->type !== 'product'));
        }

        return view('storefront.search', [
            'term' => $term,
            'products' => $products,
            'links' => $links,
            'popular' => $search->popular($locale),
        ]);
    }
}
