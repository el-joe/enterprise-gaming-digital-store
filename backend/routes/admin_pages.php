<?php

use App\Http\Controllers\Admin\Pages\AuthPageController;
use App\Http\Controllers\Admin\Pages\DashboardController;
use App\Http\Controllers\Admin\Pages\HomeController;
use App\Http\Controllers\Admin\Pages\LocaleController;
use App\Http\Controllers\Admin\Pages\OrderPageController;
use App\Http\Controllers\Admin\Pages\PaymentPageController;
use App\Http\Controllers\Admin\Pages\SupplierPageController;
use Illuminate\Support\Facades\Route;

/**
 * Admin pages (M14, Blade). Registered in bootstrap/app.php under `/admin` with the name prefix
 * `admin.` and the `admin.web` group (session, CSRF, nonce CSP, admin language). Pages only
 * read; every change goes through the JSON routes of routes/admin.php (`/admin/api`), which
 * check their own permission and write the audit log. Every page names its permission: hidden
 * navigation is a convenience, the route is the control (SECURITY.md section 4).
 */
Route::middleware('throttle:auth')->group(function (): void {
    Route::get('/login', [AuthPageController::class, 'login'])->name('login');
    Route::get('/two-factor', [AuthPageController::class, 'twoFactor'])->name('two-factor');
});
Route::post('/locale', LocaleController::class)->middleware('throttle:30,1')->name('locale');

// Signed in, active staff, before two-step verification is set up (SECURITY.md section 1).
Route::middleware(['auth', 'staff', 'admin.idle'])->group(function (): void {
    Route::get('/two-factor/setup', [AuthPageController::class, 'setup'])->name('two-factor.setup');
    Route::get('/two-factor/setup/qr', [AuthPageController::class, 'setupQr'])->name('two-factor.qr');
});

Route::middleware(['auth', 'staff', 'two_factor:redirect', 'admin.idle', 'throttle:admin'])->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::middleware('permission:orders.view')->group(function (): void {
        Route::get('/orders', [OrderPageController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderPageController::class, 'show'])->whereUlid('order')->name('orders.show');
    });

    Route::middleware('permission:suppliers.view')->group(function (): void {
        Route::get('/suppliers', [SupplierPageController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierPageController::class, 'show'])->whereNumber('supplier')->name('suppliers.show');
    });

    Route::get('/payments', [PaymentPageController::class, 'gateways'])->middleware('permission:payments.view')->name('payments.index');
    Route::get('/payments/gateways/{gateway}', [PaymentPageController::class, 'gateway'])->middleware('permission:payments.view')->whereNumber('gateway')->name('payments.gateways.show');
    Route::get('/payments/transactions', [PaymentPageController::class, 'transactions'])->middleware('permission:payments.view')->name('payments.transactions');
    Route::get('/payments/webhooks', [PaymentPageController::class, 'webhooks'])->middleware('permission:payments.webhook_logs')->name('payments.webhooks');
    Route::get('/payments/refunds', [PaymentPageController::class, 'refunds'])->middleware('permission:refunds.view')->name('payments.refunds');
    Route::get('/payments/disputes', [PaymentPageController::class, 'disputes'])->middleware('permission:payments.view')->name('payments.disputes');
    Route::get('/payments/reconciliation', [PaymentPageController::class, 'reconciliation'])->middleware('permission:payments.view')->name('payments.reconciliation');
});
