<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\DTOs\HomeItem;
use App\Domains\Cms\DTOs\HomeSection;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Services\StorefrontHomeService;
use App\Domains\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\ResolvesDisplayCurrency;
use App\Http\Resources\Storefront\BannerResource;
use App\Http\Resources\Storefront\HomeSectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ResolvesDisplayCurrency;

    public function __construct(private StorefrontHomeService $home, private ContentTranslator $translator) {}

    public function show(Request $request): JsonResponse
    {
        $currency = $this->displayCurrency($request);
        $page = $this->home->build($currency);

        $this->preloadTranslations($page->sections, array_merge(...array_values($page->banners)));

        return $this->success([
            'sections' => HomeSectionResource::collection($page->sections)->resolve(),
            'banners' => array_map(static fn (array $banners): array => BannerResource::collection($banners)->resolve(), $page->banners),
        ], meta: ['display_currency' => $currency]);
    }

    /**
     * One translation query for everything on the page (none in English).
     *
     * @param  list<HomeSection>  $sections
     * @param  list<Banner>  $banners
     */
    private function preloadTranslations(array $sections, array $banners): void
    {
        $models = [];

        foreach ($sections as $section) {
            $models[] = $section->section;

            foreach ($section->items as $item) {
                /** @var HomeItem $item */
                $payload = $item->payload;

                if ($payload instanceof ProductCard) {
                    array_push($models, $payload->product, $payload->product->gamingPlatform, $payload->product->primaryCategory);
                } elseif ($payload instanceof Review) {
                    $models[] = $payload->product;
                } else {
                    $models[] = $payload;
                }
            }
        }

        $this->translator->preload([...$models, ...$banners], ['name', 'short_description', 'title', 'subtitle', 'cta_label', 'question', 'answer']);
    }
}
