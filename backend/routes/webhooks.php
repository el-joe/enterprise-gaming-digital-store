<?php

use App\Http\Controllers\Webhooks\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/**
 * Provider webhooks under /webhooks: outside the `web` group, so no session, cookies or CSRF;
 * the raw body is read unmodified for signature verification. Rate limited per IP.
 * `/webhooks/suppliers/{key}` stays reserved for supplier webhooks (none routed yet).
 */
Route::post('/{gateway}', PaymentWebhookController::class)
    ->where('gateway', '(?!suppliers$)[a-z0-9_]{2,32}')
    ->middleware('throttle:webhooks')
    ->name('payments');
