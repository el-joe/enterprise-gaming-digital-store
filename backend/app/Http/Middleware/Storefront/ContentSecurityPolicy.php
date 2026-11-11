<?php

namespace App\Http\Middleware\Storefront;

use App\Domains\Analytics\Services\AnalyticsSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Content Security Policy for storefront pages (M12, SECURITY.md). Scripts and styles come from
 * this origin (Vite build) or carry the per-request nonce; the only third party is Stripe.js,
 * loaded on checkout when Stripe is offered. Inline scripts and styles without the nonce are
 * refused, so page data travels in nonced `application/json` blocks and data attributes.
 *
 * M18: on storefront pages (`:storefront`) and only while a GTM or GA4 tag is configured, the
 * Google Tag Manager script host and the GA4 collection hosts are added (the tag itself loads
 * only after analytics consent). Admin pages never allow them.
 */
class ContentSecurityPolicy
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $surface = 'admin'): Response
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        if (str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Content-Security-Policy', $this->policy($nonce, $surface === 'storefront'));
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Referrer-Policy', $response->headers->get('Referrer-Policy') ?? 'strict-origin-when-cross-origin');
        }

        return $response;
    }

    private function policy(string $nonce, bool $storefront): string
    {
        $dev = $this->viteDevServer();
        $analytics = $storefront ? app(AnalyticsSettings::class)->cspSources() : ['script' => [], 'connect' => [], 'img' => []];

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-{$nonce}'", 'https://js.stripe.com', ...$analytics['script'], ...$dev],
            'style-src' => ["'self'", "'nonce-{$nonce}'", ...$dev],
            'img-src' => ["'self'", 'data:', 'https:'],
            'font-src' => ["'self'", ...$dev],
            'connect-src' => ["'self'", 'https://api.stripe.com', ...$analytics['connect'], ...$dev, ...array_map(static fn (string $origin): string => preg_replace('/^http/', 'ws', $origin), $dev)],
            'frame-src' => ['https://js.stripe.com', 'https://hooks.stripe.com'],
            'form-action' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-ancestors' => ["'none'"],
        ];

        return implode('; ', array_map(static fn (string $name, array $sources): string => $name.' '.implode(' ', $sources), array_keys($directives), $directives));
    }

    /**
     * The Vite dev server origin while `npm run dev` runs (local only), so hot reload works.
     *
     * @return list<string>
     */
    private function viteDevServer(): array
    {
        if (! Vite::isRunningHot()) {
            return [];
        }

        $url = trim((string) File::get(Vite::hotFile()));
        $parts = parse_url($url);

        return isset($parts['scheme'], $parts['host']) ? [$parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '')] : [];
    }
}
