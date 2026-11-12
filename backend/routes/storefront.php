<?php

use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CountryController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductReviewController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\TaxonomyController;
use Illuminate\Support\Facades\Route;

/**
 * Public, anonymous storefront reads, registered twice in the stateless `api` group
 * (bootstrap/app.php, M-A4): under /api/v1 for the web and under /api/v1/mobile for the app,
 * with the same controllers. No cookie is set; the display currency comes from `X-Currency`
 * (or the web `currency` cookie) and the language from `Accept-Language`.
 */
Route::prefix('storefront')->name('storefront.')->group(function (): void {
    Route::middleware('throttle:storefront')->group(function (): void {
        Route::get('/home', [HomeController::class, 'show'])->middleware('cache.public:60,currency')->name('home');
        Route::get('/platforms', [TaxonomyController::class, 'platforms'])->middleware('cache.public:300')->name('platforms');
        Route::get('/categories', [TaxonomyController::class, 'categories'])->middleware('cache.public:300')->name('categories');
        Route::get('/navigation', [TaxonomyController::class, 'navigation'])->middleware('cache.public:300')->name('navigation');
        Route::get('/countries', [CountryController::class, 'index'])->middleware('cache.public:3600')->name('countries');
        Route::get('/catalog/products', [CatalogController::class, 'products'])->name('catalog.products');
        Route::get('/catalog/facets', [CatalogController::class, 'facets'])->name('catalog.facets');
        Route::get('/catalog/products/{slug}', [CatalogController::class, 'show'])->middleware('cache.public:60,currency')->name('catalog.show');
        Route::get('/catalog/products/{product:slug}/variants', [CatalogController::class, 'variants'])->name('catalog.variants');
        Route::get('/catalog/products/{product:slug}/quick-add', [CatalogController::class, 'quickAdd'])->name('catalog.quick-add');
        Route::get('/catalog/products/{product:slug}/reviews', [ProductReviewController::class, 'index'])->name('catalog.reviews');
        Route::get('/currencies', [CatalogController::class, 'currencies'])->name('currencies');
    });

    Route::middleware('throttle:search')->group(function (): void {
        Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
        Route::get('/search/popular', [SearchController::class, 'popular'])->name('search.popular');
    });
});
