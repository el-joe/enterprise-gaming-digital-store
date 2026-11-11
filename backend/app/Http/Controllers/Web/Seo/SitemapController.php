<?php

namespace App\Http\Controllers\Web\Seo;

use App\Domains\Seo\Sitemaps\SitemapGenerator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * `/sitemap.xml` (the index) and `/sitemaps/{file}` (children), served from the files
 * `seo:generate-sitemaps` writes; generated on the first request when none exist (M18).
 */
class SitemapController extends Controller
{
    public function __construct(private SitemapGenerator $sitemaps) {}

    public function index(): Response
    {
        if (! $this->sitemaps->exists()) {
            $this->sitemaps->generate();
        }

        return $this->xml($this->sitemaps->read(SitemapGenerator::INDEX));
    }

    public function show(string $file): Response
    {
        return $this->xml($this->sitemaps->read($file));
    }

    private function xml(?string $content): Response
    {
        if ($content === null) {
            throw new NotFoundHttpException;
        }

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age='.(int) config('seo.sitemaps.max_age_seconds', 3600),
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
