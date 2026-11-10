<?php

use App\Http\Controllers\Storefront\Auth\EmailVerificationController;
use App\Http\Controllers\Storefront\Auth\LoginController;
use App\Http\Controllers\Storefront\Auth\NewPasswordController;
use App\Http\Controllers\Storefront\Auth\PasswordResetLinkController;
use App\Http\Controllers\Storefront\Auth\RegisterController;
use App\Http\Controllers\Storefront\Auth\TwoFactorChallengeController;
use App\Http\Middleware\Storefront\ContentSecurityPolicy;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:auth')->name('register');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth')->name('login');
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:otp')->name('two-factor.challenge');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:auth')->name('password.email');
// M12: a browser gets the reset page (CSP nonce, language the shopper last browsed in).
Route::get('/reset-password/{token}', [NewPasswordController::class, 'show'])->middleware([ContentSecurityPolicy::class, 'storefront.locale'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:auth')->name('password.update');

// M-A5 (G10): no session needed; the controller checks the signature, the id and the hash.
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
