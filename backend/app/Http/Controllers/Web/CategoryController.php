<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontTaxonomyService;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RendersProductListing;
use App\Http\Requests\Web\ListingPageRequest;
use App\Http\Resources\Storefront\CatalogNodeResource;
use App\Http\Support\LocalizedRoutes;
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

        $node = $categories->first(static fn (CatalogNode $node): bool => $node->model->slug === $slug)
            ?? throw (new ModelNotFoundException)->setModel(Category::class);
        $category = (new CatalogNodeResource($node))->resolve($request);
        $listing = $this->productListing($request, $storefront->currency($request), ['category' => $slug]);

        $seo = $this->listingSeo(
            SeoTemplatePageType::Category,
            $node->model,
            (string) $category['name'],
            (string) $category['name'],
            $category['description'] ?: __('storefront.meta.category', ['name' => $category['name']]),
            $listing['products'],
            LocalizedRoutes::url('storefront.category', ['slug' => $slug]),
        );

        return view('storefront.category', [
            'category' => $category,
            'platforms' => CatalogNodeResource::collection($platforms)->resolve($request),
            'seo' => $seo,
            ...$listing,
        ]);
    }
}
