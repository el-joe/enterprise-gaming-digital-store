<?php

use App\Http\Controllers\Mobile\AppConfigController;
use App\Http\Controllers\Storefront\CurrencyController;
use Illuminate\Support\Facades\Route;

/**
 * Public JSON under /api/v1 that is not part of the shared storefront reads (routes/storefront.php,
 * registered for both the web and the app). The currency route sets a cookie, so the app sends
 * `X-Currency` instead and it has no /api/v1/mobile twin.
 */
Route::prefix('storefront')->name('storefront.')->middleware('throttle:storefront')->group(function (): void {
    Route::post('/currency', [CurrencyController::class, 'store'])->name('currency.store');
});

Route::get('/app/config', [AppConfigController::class, 'show'])->middleware(['throttle:storefront', 'cache.public:60'])->name('app.config');
