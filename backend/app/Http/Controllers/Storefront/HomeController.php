<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Cms\Services\HomeTranslations;
use App\Domains\Cms\Services\StorefrontHomeService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\ResolvesDisplayCurrency;
use App\Http\Resources\Storefront\BannerResource;
use App\Http\Resources\Storefront\HomeSectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ResolvesDisplayCurrency;

    public function __construct(private StorefrontHomeService $home, private HomeTranslations $translations) {}

    public function show(Request $request): JsonResponse
    {
        $currency = $this->displayCurrency($request);
        $page = $this->home->build($currency);

        $this->translations->preload($page);

        return $this->success([
            'sections' => HomeSectionResource::collection($page->sections)->resolve(),
            'banners' => array_map(static fn (array $banners): array => BannerResource::collection($banners)->resolve(), $page->banners),
        ], meta: ['display_currency' => $currency]);
    }
}
