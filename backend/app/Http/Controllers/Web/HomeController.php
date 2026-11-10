<?php

namespace App\Http\Controllers\Web;

use App\Domains\Cms\Services\HomeTranslations;
use App\Domains\Cms\Services\StorefrontHomeService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\BannerResource;
use App\Http\Resources\Storefront\HomeSectionResource;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home page (M12): the admin-managed sections and banners of the home JSON (M-A4), rendered on
 * the server in the display currency.
 */
class HomeController extends Controller
{
    private const array EMAIL_VERIFICATION_STATUSES = ['verified', 'already_verified', 'invalid'];

    public function show(Request $request, StorefrontHomeService $home, HomeTranslations $translations, StorefrontRequest $storefront): View
    {
        $page = $home->build($storefront->currency($request));
        $translations->preload($page);

        $verification = $request->query('email_verification');

        return view('storefront.home', [
            'sections' => HomeSectionResource::collection($page->sections)->resolve($request),
            'heroBanners' => BannerResource::collection($page->banners['hero'] ?? [])->resolve($request),
            'promoBanners' => BannerResource::collection($page->banners['home_promo'] ?? [])->resolve($request),
            'emailVerification' => in_array($verification, self::EMAIL_VERIFICATION_STATUSES, true) ? $verification : null,
        ]);
    }
}
