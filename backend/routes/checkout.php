<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\GuestOrderCodeController;
use App\Http\Controllers\Storefront\GuestOrderController;
use App\Http\Controllers\Storefront\PaymentController;
use App\Http\Middleware\Storefront\ValidateGuestAccessSignature;
use Illuminate\Support\Facades\Route;

/**
 * Cart and checkout JSON, registered twice (bootstrap/app.php, ADR-036):
 * - under /api/v1 in the `web` group for the browser: session (signed-in customers, guest order
 *   access), CSRF on writes, guests identified by the encrypted, HTTP-only `cart_token` cookie;
 * - under /api/v1/mobile in the stateless `api` group for the app: optional customer bearer
 *   token, `X-Cart-Token`, `X-Currency` and `X-Guest-Order-Token` headers, no cookies or CSRF.
 * The controllers tell the two apart with `$request->hasSession()`.
 */
Route::prefix('storefront/cart')->name('storefront.cart.')->middleware('throttle:storefront')->group(function (): void {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/items', [CartController::class, 'store'])->name('items.store');
    Route::patch('/items/{cartItem}', [CartController::class, 'update'])->whereNumber('cartItem')->name('items.update');
    Route::delete('/items/{cartItem}', [CartController::class, 'destroy'])->whereNumber('cartItem')->name('items.destroy');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->middleware('throttle:checkout')->name('coupon.store');
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('coupon.destroy');
});

Route::prefix('checkout')->name('checkout.')->group(function (): void {
    Route::middleware('throttle:checkout')->group(function (): void {
        Route::post('/validate', [CheckoutController::class, 'check'])->name('validate');
        Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('place-order');
        Route::get('/orders/{orderPublicId}/status', [CheckoutController::class, 'status'])->name('orders.status');
        Route::get('/payment-options', [PaymentController::class, 'options'])->name('payment-options');
        Route::post('/orders/{orderPublicId}/retry-payment', [PaymentController::class, 'retry'])->name('orders.retry-payment');
        Route::post('/orders/{orderPublicId}/verify-payment', [PaymentController::class, 'verify'])->name('orders.verify-payment');
        Route::post('/orders/{orderPublicId}/paypal/capture', [PaymentController::class, 'capture'])->name('orders.paypal.capture');
    });

    Route::get('/guest/orders/{orderPublicId}/access', [GuestOrderCodeController::class, 'access'])
        ->middleware([ValidateGuestAccessSignature::class, 'throttle:otp'])->name('guest.orders.access');
    Route::post('/guest/orders/{order}/units/{unit}/reveal', [GuestOrderCodeController::class, 'reveal'])
        ->middleware('throttle:code-reveal')->name('guest.orders.units.reveal');

    Route::middleware('throttle:otp')->group(function (): void {
        Route::post('/guest/otp/send', [GuestOrderController::class, 'send'])->name('guest.otp.send');
        Route::post('/guest/otp/verify', [GuestOrderController::class, 'verify'])->name('guest.otp.verify');
    });
});
