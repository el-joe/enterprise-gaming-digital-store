<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontTaxonomyService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RendersProductListing;
use App\Http\Requests\Web\ListingPageRequest;
use App\Http\Resources\Storefront\CatalogNodeResource;
use App\Http\Support\StorefrontRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;

/**
 * A category listing (`/{slug}`, e.g. `/gift-cards`) with filters, sort and pagination.
 */
class CategoryController extends Controller
{
    use RendersProductListing;

    public function show(ListingPageRequest $request, string $slug, StorefrontTaxonomyService $taxonomy, ContentTranslator $translator, StorefrontRequest $storefront): View
    {
        $categories = $taxonomy->categories();
        $platforms = $taxonomy->platforms();
        $translator->preload([...$categories->map(static fn (CatalogNode $node) => $node->model), ...$platforms->map(static fn (CatalogNode $node) => $node->model)], ['name', 'description']);

        $category = collect(CatalogNodeResource::collection($categories)->resolve($request))->firstWhere('slug', $slug)
            ?? throw (new ModelNotFoundException)->setModel(Category::class);

        return view('storefront.category', [
            'category' => $category,
            'platforms' => CatalogNodeResource::collection($platforms)->resolve($request),
            ...$this->productListing($request, $storefront->currency($request), ['category' => $slug]),
        ]);
    }
}
