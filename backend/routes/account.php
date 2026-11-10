<?php

use App\Http\Controllers\Account\ConfirmPasswordController;
use App\Http\Controllers\Account\OrderCodeController;
use App\Http\Controllers\Account\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::post('/security/two-factor', [TwoFactorController::class, 'store'])->name('two-factor.store');
Route::post('/security/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
Route::delete('/security/two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');

Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])->middleware('throttle:auth')->name('confirm-password');
Route::post('/orders/{order}/units/{unit}/reveal', [OrderCodeController::class, 'reveal'])
    ->middleware('throttle:code-reveal')->name('orders.units.reveal');
