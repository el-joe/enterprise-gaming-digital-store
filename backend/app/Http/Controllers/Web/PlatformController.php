<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\DTOs\CatalogNode;
use App\Domains\Catalog\Models\GamingPlatform;
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
use Illuminate\Support\Collection;
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
        $nodes = $this->taxonomy->platforms();
        $platforms = $this->platforms($nodes);
        $platform = collect($platforms)->firstWhere('slug', $slug) ?? throw (new ModelNotFoundException)->setModel(GamingPlatform::class);
        $model = $nodes->first(static fn (CatalogNode $node): bool => $node->model->slug === $slug)?->model;
        $listing = $this->productListing($request, $storefront->currency($request), ['platform' => $slug]);

        $seo = $this->listingSeo(
            SeoTemplatePageType::Platform,
            $model ?? throw (new ModelNotFoundException)->setModel(GamingPlatform::class),
            (string) $platform['name'],
            __('storefront.platforms.page_title', ['name' => $platform['name']]),
            $platform['description'] ?: __('storefront.meta.platform', ['name' => $platform['name']]),
            $listing['products'],
            LocalizedRoutes::url('storefront.platforms.show', ['slug' => $slug]),
        );

        return view('storefront.platforms.show', [
            'platform' => $platform,
            'index' => array_search($platform, $platforms, true),
            'categories' => CatalogNodeResource::collection($this->taxonomy->categories())->resolve($request),
            'seo' => $seo,
            ...$listing,
        ]);
    }

    /**
     * @param  Collection<int, CatalogNode>|null  $platforms
     * @return list<array<string, mixed>>
     */
    private function platforms(?Collection $platforms = null): array
    {
        $platforms ??= $this->taxonomy->platforms();
        $this->translator->preload($platforms->map(static fn (CatalogNode $node) => $node->model), ['name', 'description']);

        return CatalogNodeResource::collection($platforms)->resolve();
    }
}
