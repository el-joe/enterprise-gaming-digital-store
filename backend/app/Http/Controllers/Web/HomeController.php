<?php

namespace App\Http\Controllers\Web;

use App\Domains\Analytics\Services\AnalyticsSettings;
use App\Domains\Cms\Services\HomeTranslations;
use App\Domains\Cms\Services\StorefrontHomeService;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Domains\Seo\StructuredData\StructuredDataBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\BannerResource;
use App\Http\Resources\Storefront\HomeSectionResource;
use App\Http\Support\LocalizedRoutes;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home page (M12): the admin-managed sections and banners of the home JSON (M-A4), rendered on
 * the server in the display currency. M18: SEO template values, and FAQPage JSON-LD from the FAQ
 * sections only while the `faq_schema` setting is on (off by default, SEO_STRATEGY.md section 4).
 */
class HomeController extends Controller
{
    private const array EMAIL_VERIFICATION_STATUSES = ['verified', 'already_verified', 'invalid'];

    public function show(Request $request, StorefrontHomeService $home, HomeTranslations $translations, StorefrontRequest $storefront, SeoResolver $seo, AnalyticsSettings $analytics, PageSchema $schema, StructuredDataBuilder $structuredData): View
    {
        $page = $home->build($storefront->currency($request));
        $translations->preload($page);
        $sections = HomeSectionResource::collection($page->sections)->resolve($request);

        if ($analytics->faqSchemaEnabled()) {
            $faqs = collect($sections)->flatMap(static fn (array $section): array => $section['items'])
                ->filter(static fn (array $item): bool => $item['type'] === 'faq')
                ->map(static fn (array $item): array => ['question' => (string) $item['data']['question'], 'answer' => (string) $item['data']['answer']])
                ->values()->all();
            $schema->add($structuredData->faqPage($faqs, LocalizedRoutes::url('storefront.home')));
        }

        $verification = $request->query('email_verification');

        return view('storefront.home', [
            'seo' => $seo->resolve(SeoTemplatePageType::Home, null, [], '', __('storefront.meta.description')),
            'sections' => $sections,
            'heroBanners' => BannerResource::collection($page->banners['hero'] ?? [])->resolve($request),
            'promoBanners' => BannerResource::collection($page->banners['home_promo'] ?? [])->resolve($request),
            'emailVerification' => in_array($verification, self::EMAIL_VERIFICATION_STATUSES, true) ? $verification : null,
        ]);
    }
}
