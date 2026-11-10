<?php

use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CurrencyController;
use App\Http\Controllers\Storefront\SearchController;
use Illuminate\Support\Facades\Route;

/**
 * Public, checkout, automation and MCP JSON routes under /api/v1.
 * Checkout arrives in M7, automation in M17 and MCP in M16.
 */
Route::prefix('storefront')->name('storefront.')->group(function (): void {
    Route::middleware('throttle:storefront')->group(function (): void {
        Route::get('/catalog/products', [CatalogController::class, 'products'])->name('catalog.products');
        Route::get('/catalog/products/{product:slug}/variants', [CatalogController::class, 'variants'])->name('catalog.variants');
        Route::get('/currencies', [CatalogController::class, 'currencies'])->name('currencies');
        Route::post('/currency', [CurrencyController::class, 'store'])->name('currency.store');
    });

    Route::middleware('throttle:search')->group(function (): void {
        Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
        Route::get('/search/popular', [SearchController::class, 'popular'])->name('search.popular');
    });
});
