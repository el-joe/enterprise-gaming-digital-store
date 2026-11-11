<?php

use App\Http\Controllers\Web\Account\DashboardController;
use App\Http\Controllers\Web\Account\DigitalCodeController;
use App\Http\Controllers\Web\Account\NotificationController;
use App\Http\Controllers\Web\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Web\Account\ProfileController;
use App\Http\Controllers\Web\Account\ReviewController;
use App\Http\Controllers\Web\Account\SecurityController;
use App\Http\Controllers\Web\Account\TicketController;
use App\Http\Controllers\Web\Account\WalletController;
use App\Http\Controllers\Web\Account\WishlistController;
use App\Http\Controllers\Web\AuthPageController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PlatformController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Route;

/**
 * Storefront pages (M12, Blade). Registered in bootstrap/app.php once per language: unprefixed
 * for the default language and under `/{locale}` with the name prefix `{locale}.` for the others
 * (SEO_STRATEGY.md section 2). Every write goes through the existing JSON routes (CSRF, session).
 * The category route catches one top-level segment, so it is registered last and never matches
 * a reserved word (`catalog.reserved_slugs`, which includes the language prefixes).
 */
Route::middleware('throttle:storefront')->group(function (): void {
    Route::get('/', [HomeController::class, 'show'])->name('storefront.home');
    Route::get('/gaming', [PlatformController::class, 'index'])->name('storefront.platforms.index');
    Route::get('/gaming/{slug}', [PlatformController::class, 'show'])->name('storefront.platforms.show');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('storefront.products.show');
    Route::get('/search', [SearchController::class, 'show'])->middleware('throttle:search')->name('storefront.search');
    Route::get('/pages/{slug}', [PageController::class, 'show'])->name('storefront.pages.show');
    Route::get('/blog', [BlogController::class, 'index'])->name('storefront.blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('storefront.blog.show');

    Route::get('/cart', [CartController::class, 'show'])->name('storefront.cart');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('storefront.checkout');
    Route::get('/orders/{orderPublicId}', [OrderController::class, 'show'])->name('storefront.orders.show');

    Route::get('/login', [AuthPageController::class, 'login'])->name('storefront.login');
    Route::get('/register', [AuthPageController::class, 'register'])->name('storefront.register');
    Route::get('/two-factor-challenge', [AuthPageController::class, 'twoFactor'])->name('storefront.two-factor');
    Route::get('/forgot-password', [AuthPageController::class, 'forgotPassword'])->name('storefront.forgot-password');
    Route::get('/email/verify', [AuthPageController::class, 'verifyEmail'])->middleware('auth')->name('storefront.verify-email');

    // M13: customer account (spec section 9). Reads only; every change goes through the JSON
    // routes of routes/account.php with CSRF. Other customers' records are 404.
    Route::middleware(['auth', 'account.active'])->prefix('account')->name('storefront.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'show'])->name('account');
        Route::get('/orders', [AccountOrderController::class, 'index'])->name('account.orders');
        Route::get('/orders/{orderPublicId}', [AccountOrderController::class, 'show'])->whereUlid('orderPublicId')->name('account.orders.show');
        Route::get('/codes', [DigitalCodeController::class, 'index'])->name('account.codes');
        Route::get('/wallet', [WalletController::class, 'show'])->name('account.wallet');
        Route::get('/wishlist', [WishlistController::class, 'show'])->name('account.wishlist');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('account.reviews');
        Route::get('/tickets', [TicketController::class, 'index'])->name('account.tickets');
        Route::get('/tickets/new', [TicketController::class, 'create'])->name('account.tickets.create');
        Route::get('/tickets/{ticketPublicId}', [TicketController::class, 'show'])->whereUlid('ticketPublicId')->name('account.tickets.show');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('account.notifications');
        Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('account.notifications.preferences');
        Route::get('/profile', [ProfileController::class, 'show'])->name('account.profile');
        Route::get('/security', [SecurityController::class, 'show'])->name('account.security');
        Route::get('/security/two-factor/qr', [SecurityController::class, 'twoFactorQr'])->name('account.two-factor-qr');
    });

    $reserved = implode('|', array_map(static fn (string $slug): string => preg_quote($slug, '/'), (array) config('catalog.reserved_slugs')));

    Route::get('/{slug}', [CategoryController::class, 'show'])
        ->where('slug', "(?!(?:{$reserved})$)[a-z0-9]+(?:-[a-z0-9]+)*")
        ->name('storefront.category');
});
