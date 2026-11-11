<?php

namespace App\Http\Controllers\Web\Seo;

use App\Http\Controllers\Controller;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Http\Response;

/**
 * `robots.txt` (SEO_STRATEGY.md section 5, M18): public content allowed, private and
 * personal areas disallowed in every language prefix, CSS and JS never blocked, and the
 * sitemap index referenced. `seo.robots.block_all` answers `Disallow: /` (staging).
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = ['User-agent: *'];

        if (config('seo.robots.block_all')) {
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = 'Allow: /';

            foreach ((array) config('seo.robots.disallow') as $path) {
                $lines[] = 'Disallow: '.$path;

                if ($path !== '/admin' && $path !== '/api') {
                    foreach (LocalizedRoutes::prefixedLocales() as $locale) {
                        $lines[] = 'Disallow: /'.$locale.$path;
                    }
                }
            }

            $lines[] = '';
            $lines[] = 'Sitemap: '.url('/sitemap.xml');
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
