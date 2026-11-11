<?php

use App\Http\Controllers\Account\ConfirmPasswordController;
use App\Http\Controllers\Account\DeviceController;
use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\NotificationPreferenceController;
use App\Http\Controllers\Account\OrderCodeController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\RefundRequestController;
use App\Http\Controllers\Account\ReviewController;
use App\Http\Controllers\Account\SupportTicketController;
use App\Http\Controllers\Account\TwoFactorController;
use App\Http\Controllers\Account\WalletController;
use App\Http\Controllers\Account\WishlistController;
use App\Http\Controllers\Storefront\Auth\EmailVerificationController;
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
    Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->whereNumber('review')->name('reviews.update');

    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->whereUlid('ticket')->name('tickets.show');
    Route::post('/tickets/{ticket}/messages', [SupportTicketController::class, 'reply'])->whereUlid('ticket')->name('tickets.messages.store');

    Route::get('/refund-requests', [RefundRequestController::class, 'index'])->name('refund-requests.index');
    Route::post('/refund-requests', [RefundRequestController::class, 'store'])->name('refund-requests.store');
});

// M-A5: order history and detail, profile, password, devices, notification inbox and preferences
// (gaps G4, G5, G6 inbox). Registered for the web session and the mobile bearer token alike.
Route::put('/password', [PasswordController::class, 'update'])->middleware('throttle:auth')->name('password.update');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');

Route::middleware('throttle:account')->group(static function (): void {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereUlid('order')->name('orders.show');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices/sign-out-others', [DeviceController::class, 'signOutOthers'])->middleware('throttle:auth')->name('devices.sign-out-others');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->where('device', '[A-Za-z0-9_]{1,64}')->name('devices.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->whereUuid('notification')->name('notifications.read');

    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show'])->name('notification-preferences.show');
    Route::patch('/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');
});

// M13: wishlist (spec section 9)
Route::middleware('throttle:account')->group(static function (): void {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{slug}', [WishlistController::class, 'destroy'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('wishlist.destroy');
});
