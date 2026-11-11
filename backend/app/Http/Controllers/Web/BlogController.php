<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Services\BlogPostService;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Domains\Seo\StructuredData\StructuredDataBuilder;
use App\Domains\Shared\Services\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Blog (`/blog`, `/blog/{slug}`, SEO_STRATEGY.md section 2, M18): visible posts newest first and
 * one post with BlogPosting JSON-LD. Text through ContentTranslator (Arabic from
 * `content_translations`), body HTML through the sanitizer allow-list.
 */
class BlogController extends Controller
{
    private const int PER_PAGE = 12;

    public function __construct(private BlogPostService $posts, private ContentTranslator $translator, private MediaUrls $media) {}

    public function index(Request $request): View
    {
        $page = max(1, min(1000, $request->integer('page', 1)));
        $posts = $this->posts->visiblePage($page, self::PER_PAGE)->withQueryString();
        $this->translator->preload($posts->items(), ['title', 'excerpt']);

        $posts->setCollection($posts->getCollection()->map(fn (BlogPost $post): array => [
            'slug' => $post->slug,
            'title' => (string) $this->translator->text($post, 'title'),
            'excerpt' => $this->translator->text($post, 'excerpt'),
            'published_at' => $post->published_at,
            'cover' => $post->coverMedia === null ? null : $this->media->image($post->coverMedia),
        ]));

        return view('storefront.blog.index', ['posts' => $posts]);
    }

    public function show(string $slug, HtmlSanitizer $sanitizer, SeoResolver $seo, PageSchema $schema, StructuredDataBuilder $structuredData): View
    {
        $post = $this->posts->findVisible($slug) ?? throw new NotFoundHttpException;
        $this->translator->preload([$post], ['title', 'excerpt', 'body']);

        $title = (string) $this->translator->text($post, 'title');
        $excerpt = $this->translator->text($post, 'excerpt');
        $body = $sanitizer->sanitize((string) $this->translator->text($post, 'body'));
        $cover = $post->coverMedia === null ? null : $this->media->image($post->coverMedia);
        $resolved = $seo->resolve(SeoTemplatePageType::Blog, $post, ['name' => $title], $title, $excerpt ?? $body);

        $schema->add($structuredData->blogPosting(
            $title,
            $resolved->description,
            LocalizedRoutes::url('storefront.blog.show', ['slug' => $post->slug]),
            app()->getLocale(),
            $post->published_at,
            $post->updated_at,
            $resolved->image ?? $cover['url'] ?? null,
        ));

        return view('storefront.blog.show', [
            'post' => ['slug' => $post->slug, 'title' => $title, 'body' => $body, 'published_at' => $post->published_at, 'cover' => $cover],
            'seo' => $resolved,
        ]);
    }
}
