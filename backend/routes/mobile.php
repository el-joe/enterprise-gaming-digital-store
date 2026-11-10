<?php

use App\Http\Controllers\Mobile\AppConfigController;
use App\Http\Controllers\Mobile\Auth\LoginController;
use App\Http\Controllers\Mobile\Auth\MeController;
use App\Http\Controllers\Mobile\Auth\RegisterController;
use App\Http\Controllers\Mobile\Auth\TokenController;
use App\Http\Controllers\Mobile\Auth\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

/**
 * Mobile app JSON under /api/v1/mobile (M-A2, ADR-035). Stateless `api` group: no session, no
 * cookies, no CSRF. Signed-in routes need a customer bearer token (`mobile.customer` group).
 */
Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:auth')->name('register');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth')->name('login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:otp')->name('two-factor.challenge');
    Route::post('/refresh', [TokenController::class, 'refresh'])->middleware('throttle:mobile-refresh')->name('refresh');

    Route::middleware('mobile.customer')->group(function (): void {
        Route::get('/me', [MeController::class, 'show'])->name('me');
        Route::post('/logout', [TokenController::class, 'destroy'])->name('logout');
        Route::post('/logout-all', [TokenController::class, 'destroyAll'])->name('logout-all');
    });
});

// M-A4: the same public app configuration as GET /api/v1/app/config, under the app's base path.
Route::get('/app/config', [AppConfigController::class, 'show'])->middleware(['throttle:storefront', 'cache.public:60'])->name('app.config');
