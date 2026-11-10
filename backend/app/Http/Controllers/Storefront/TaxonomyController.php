<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontTaxonomyService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\CatalogNodeResource;
use Illuminate\Http\JsonResponse;

class TaxonomyController extends Controller
{
    public function __construct(private StorefrontTaxonomyService $taxonomy, private ContentTranslator $translator) {}

    public function platforms(): JsonResponse
    {
        $platforms = $this->taxonomy->platforms();
        $this->translator->preload($platforms->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        return $this->success(['platforms' => CatalogNodeResource::collection($platforms)->resolve()]);
    }

    public function categories(): JsonResponse
    {
        $categories = $this->taxonomy->categories();
        $this->translator->preload($categories->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        return $this->success(['categories' => CatalogNodeResource::collection($categories)->resolve()]);
    }
}
