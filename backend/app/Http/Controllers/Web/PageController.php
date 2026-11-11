<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\Services\PageService;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Shared\Services\HtmlSanitizer;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Content and legal pages (`/pages/{slug}`). A published CMS page wins; the store's built-in
 * pages (about, contact, terms, privacy, refund policy) are the fallback until the owner
 * publishes their own text in the CMS.
 */
class PageController extends Controller
{
    public const array BUILT_IN = ['about', 'contact', 'terms', 'privacy', 'refund-policy'];

    public function show(string $slug, PageService $pages, ContentTranslator $translator, HtmlSanitizer $sanitizer, SeoResolver $seo): View
    {
        $page = $pages->findPublished($slug);

        if ($page !== null) {
            $translator->preload([$page], ['title', 'body']);
            $title = (string) $translator->text($page, 'title');
            $body = $sanitizer->sanitize((string) $translator->text($page, 'body'));

            return view('storefront.pages.show', [
                'slug' => $slug,
                'title' => $title,
                'body' => $body,
                'updatedAt' => $page->updated_at,
                'seo' => $seo->resolve(SeoTemplatePageType::Page, $page, ['name' => $title], $title, $body),
            ]);
        }

        if (! in_array($slug, self::BUILT_IN, true)) {
            throw new NotFoundHttpException;
        }

        $title = (string) __("storefront.pages.{$slug}.title");
        $paragraphs = (array) __("storefront.pages.{$slug}.body");

        return view('storefront.pages.show', [
            'slug' => $slug,
            'title' => $title,
            'body' => null,
            'updatedAt' => null,
            'seo' => $seo->resolve(SeoTemplatePageType::Page, null, ['name' => $title], $title, (string) ($paragraphs[0] ?? '')),
        ]);
    }
}
