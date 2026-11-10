<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\Services\PageService;
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

    public function show(string $slug, PageService $pages, ContentTranslator $translator, HtmlSanitizer $sanitizer): View
    {
        $page = $pages->findPublished($slug);

        if ($page !== null) {
            $translator->preload([$page], ['title', 'body']);

            return view('storefront.pages.show', [
                'slug' => $slug,
                'title' => (string) $translator->text($page, 'title'),
                'body' => $sanitizer->sanitize((string) $translator->text($page, 'body')),
                'updatedAt' => $page->updated_at,
            ]);
        }

        if (! in_array($slug, self::BUILT_IN, true)) {
            throw new NotFoundHttpException;
        }

        return view('storefront.pages.show', ['slug' => $slug, 'title' => __("storefront.pages.{$slug}.title"), 'body' => null, 'updatedAt' => null]);
    }
}
