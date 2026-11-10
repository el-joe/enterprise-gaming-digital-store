<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\GamingPlatform;
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
 * Gaming platforms (`/gaming`) and one platform with its products (`/gaming/{slug}`).
 */
class PlatformController extends Controller
{
    use RendersProductListing;

    public function __construct(private StorefrontTaxonomyService $taxonomy, private ContentTranslator $translator) {}

    public function index(): View
    {
        return view('storefront.platforms.index', ['platforms' => $this->platforms()]);
    }

    public function show(ListingPageRequest $request, string $slug, StorefrontRequest $storefront): View
    {
        $platforms = $this->platforms();
        $platform = collect($platforms)->firstWhere('slug', $slug) ?? throw (new ModelNotFoundException)->setModel(GamingPlatform::class);

        return view('storefront.platforms.show', [
            'platform' => $platform,
            'index' => array_search($platform, $platforms, true),
            'categories' => CatalogNodeResource::collection($this->taxonomy->categories())->resolve($request),
            ...$this->productListing($request, $storefront->currency($request), ['platform' => $slug]),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function platforms(): array
    {
        $platforms = $this->taxonomy->platforms();
        $this->translator->preload($platforms->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        return CatalogNodeResource::collection($platforms)->resolve();
    }
}
