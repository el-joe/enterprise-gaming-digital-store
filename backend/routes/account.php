<?php

use App\Http\Controllers\Account\ConfirmPasswordController;
use App\Http\Controllers\Account\OrderCodeController;
use App\Http\Controllers\Account\RefundRequestController;
use App\Http\Controllers\Account\ReviewController;
use App\Http\Controllers\Account\SupportTicketController;
use App\Http\Controllers\Account\TwoFactorController;
use App\Http\Controllers\Account\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/security/two-factor', [TwoFactorController::class, 'store'])->name('two-factor.store');
Route::post('/security/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
Route::delete('/security/two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');

Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])->middleware('throttle:auth')->name('confirm-password');
Route::post('/orders/{order}/units/{unit}/reveal', [OrderCodeController::class, 'reveal'])
    ->middleware('throttle:code-reveal')->name('orders.units.reveal');

// M11: wallet (read only, BD3), reviews, support tickets and refund requests
Route::middleware('throttle:account')->group(static function (): void {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/eligible', [ReviewController::class, 'eligible'])->name('reviews.eligible');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->whereUlid('ticket')->name('tickets.show');
    Route::post('/tickets/{ticket}/messages', [SupportTicketController::class, 'reply'])->whereUlid('ticket')->name('tickets.messages.store');

    Route::get('/refund-requests', [RefundRequestController::class, 'index'])->name('refund-requests.index');
    Route::post('/refund-requests', [RefundRequestController::class, 'store'])->name('refund-requests.store');
});
