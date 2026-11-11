<?php

use App\Http\Controllers\Web\Seo\RobotsController;
use App\Http\Controllers\Web\Seo\SitemapController;
use Illuminate\Support\Facades\Route;

/**
 * Crawler files (M18, SEO_STRATEGY.md section 5). No session, cookies or CSRF: the same body
 * for every caller. Registered before the storefront pages.
 */
Route::middleware('throttle:storefront')->group(function (): void {
    Route::get('/robots.txt', RobotsController::class)->name('seo.robots');
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('seo.sitemap');
    Route::get('/sitemaps/{file}', [SitemapController::class, 'show'])
        ->where('file', '[a-z]+-[a-z]{2}-[0-9]{1,5}\.xml')
        ->name('seo.sitemaps.show');
});
