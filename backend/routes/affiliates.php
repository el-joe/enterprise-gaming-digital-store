<?php

use App\Http\Controllers\Storefront\AffiliateController;
use Illuminate\Support\Facades\Route;

/**
 * Affiliate click tracking under /api/v1, `web` group: it sets the encrypted `aff_ref` cookie of
 * the browser, so it has no stateless twin (split from routes/checkout.php in M-A3).
 */
Route::post('storefront/affiliates/click', [AffiliateController::class, 'click'])->middleware('throttle:storefront')->name('storefront.affiliates.click');
