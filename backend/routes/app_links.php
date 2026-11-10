<?php

use App\Http\Controllers\Mobile\AppLinkAssociationController;
use App\Http\Controllers\Mobile\AppPaymentReturnController;
use Illuminate\Support\Facades\Route;

/**
 * App links (M-A6, ADR-039), registered without the `web` group (bootstrap/app.php): no
 * session, cookies or CSRF, so the association files stay cacheable and a return link sets
 * nothing in the browser. Nginx must pass `/.well-known/` and `/app/` to Laravel
 * (DEPLOYMENT.md 6); no static file may exist under `public/.well-known/`.
 */
Route::get('/.well-known/assetlinks.json', [AppLinkAssociationController::class, 'android'])
    ->middleware('cache.public:'.(int) config('mobile.app_links.cache_seconds'))->name('app-links.assetlinks');
Route::get('/.well-known/apple-app-site-association', [AppLinkAssociationController::class, 'apple'])
    ->middleware('cache.public:'.(int) config('mobile.app_links.cache_seconds'))->name('app-links.apple');

Route::prefix('app/payments')->name('app.payments.')->middleware('throttle:storefront')->group(function (): void {
    Route::get('/return/{order}', [AppPaymentReturnController::class, 'return'])->whereUlid('order')->name('return');
    Route::get('/cancel/{order}', [AppPaymentReturnController::class, 'cancel'])->whereUlid('order')->name('cancel');
});
