<?php

use App\Http\Controllers\Mobile\AppConfigController;
use App\Http\Controllers\Mobile\Auth\BiometricController;
use App\Http\Controllers\Mobile\Auth\LoginController;
use App\Http\Controllers\Mobile\Auth\MeController;
use App\Http\Controllers\Mobile\Auth\RegisterController;
use App\Http\Controllers\Mobile\Auth\TokenController;
use App\Http\Controllers\Mobile\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Mobile\PushTokenController;
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

        // OD-1 (owner decision 6, ADR-040): device-bound biometric re-auth for code reveal.
        Route::middleware('throttle:mobile-biometric')->group(function (): void {
            Route::post('/biometric-key', [BiometricController::class, 'store'])->name('biometric-key.store');
            Route::delete('/biometric-key', [BiometricController::class, 'destroy'])->name('biometric-key.destroy');
            Route::post('/biometric-challenge', [BiometricController::class, 'challenge'])->name('biometric-challenge');
            Route::post('/confirm-biometric', [BiometricController::class, 'confirm'])->name('confirm-biometric');
        });
    });
});

// M-A18 (owner decision 9, ADR-042): this device's FCM push token.
Route::middleware(['mobile.customer', 'throttle:mobile-push'])->group(function (): void {
    Route::post('/push-token', [PushTokenController::class, 'store'])->name('push-token.store');
    Route::delete('/push-token', [PushTokenController::class, 'destroy'])->name('push-token.destroy');
});

// M-A4: the same public app configuration as GET /api/v1/app/config, under the app's base path.
Route::get('/app/config', [AppConfigController::class, 'show'])->middleware(['throttle:storefront', 'cache.public:60'])->name('app.config');
