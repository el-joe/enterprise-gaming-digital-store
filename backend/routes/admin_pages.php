<?php

use App\Http\Controllers\Admin\Pages\AuthPageController;
use App\Http\Controllers\Admin\Pages\AutomationPageController;
use App\Http\Controllers\Admin\Pages\CatalogPageController;
use App\Http\Controllers\Admin\Pages\CustomerPageController;
use App\Http\Controllers\Admin\Pages\DashboardController;
use App\Http\Controllers\Admin\Pages\HomeController;
use App\Http\Controllers\Admin\Pages\LocaleController;
use App\Http\Controllers\Admin\Pages\OrderPageController;
use App\Http\Controllers\Admin\Pages\PaymentPageController;
use App\Http\Controllers\Admin\Pages\PricingPageController;
use App\Http\Controllers\Admin\Pages\ResourcePageController;
use App\Http\Controllers\Admin\Pages\SettingsPageController;
use App\Http\Controllers\Admin\Pages\SupplierPageController;
use App\Http\Controllers\Admin\Pages\SupportPageController;
use App\Http\Controllers\Admin\Pages\UsersPageController;
use App\Http\Support\AdminScreens\ResourceScreens;
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

    Route::middleware('permission:catalog.view')->group(function (): void {
        Route::get('/catalog/products', [CatalogPageController::class, 'products'])->name('catalog.products.index');
        Route::get('/catalog/products/create', [CatalogPageController::class, 'createProduct'])->middleware('permission:catalog.manage')->name('catalog.products.create');
        Route::get('/catalog/products/{product}', [CatalogPageController::class, 'product'])->whereUlid('product')->name('catalog.products.edit');
    });
    Route::get('/catalog/mappings', [CatalogPageController::class, 'mappings'])->middleware('permission:mappings.manage')->name('catalog.mappings.index');
    Route::get('/content/media', [CatalogPageController::class, 'media'])->middleware('permission:content.media')->name('content.media.index');

    Route::middleware('permission:customers.view')->group(function (): void {
        Route::get('/customers', [CustomerPageController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer:public_id}', [CustomerPageController::class, 'show'])->whereUlid('customer')->name('customers.show');
    });

    Route::middleware('permission:support.view')->group(function (): void {
        Route::get('/support/tickets', [SupportPageController::class, 'tickets'])->name('support.tickets.index');
        Route::get('/support/tickets/{ticket}', [SupportPageController::class, 'ticket'])->whereUlid('ticket')->name('support.tickets.show');
    });
    Route::middleware('permission:support.refund_requests')->group(function (): void {
        Route::get('/support/refund-requests', [SupportPageController::class, 'refundRequests'])->name('support.refund-requests.index');
        Route::get('/support/refund-requests/{refundRequest}', [SupportPageController::class, 'refundRequest'])->whereNumber('refundRequest')->name('support.refund-requests.show');
    });
    Route::get('/reviews', [SupportPageController::class, 'reviews'])->middleware('permission:catalog.manage')->name('reviews.index');

    Route::middleware('permission:users.manage')->group(function (): void {
        Route::get('/users/staff', [UsersPageController::class, 'index'])->name('users.staff.index');
        Route::get('/users/staff/{staff:public_id}', [UsersPageController::class, 'show'])->whereUlid('staff')->name('users.staff.show');
    });
    Route::get('/users/roles', [UsersPageController::class, 'roles'])->middleware('permission:roles.manage|users.manage')->name('users.roles.index');

    Route::get('/settings/{group?}', SettingsPageController::class)->middleware('permission:settings.view')->where('group', '[a-z_]+')->name('settings.show');

    Route::get('/automation/ai-suggestions', [AutomationPageController::class, 'aiSuggestions'])->middleware('permission:ai_suggestions.review')->name('automation.ai-suggestions.index');
    Route::get('/automation/mcp-actions', [AutomationPageController::class, 'mcpActions'])->middleware('permission:mcp_actions.approve')->name('automation.mcp-actions.index');
    Route::middleware('permission:service_accounts.manage')->group(function (): void {
        Route::get('/automation/mcp-tokens', [AutomationPageController::class, 'mcpTokens'])->name('automation.mcp-tokens.index');
        Route::get('/automation/credentials', [AutomationPageController::class, 'credentials'])->name('automation.credentials.index');
    });

    Route::get('/pricing/exchange-rates', [PricingPageController::class, 'exchangeRates'])->middleware('permission:pricing.view|currency.manage')->name('pricing.exchange-rates.index');

    // M15: declarative list, create and edit screens (App\Http\Support\AdminScreens). The list
    // and edit pages need the screen's view permission, the create page its manage permission;
    // without the manage permission the edit page is read-only and the API refuses writes.
    foreach (ResourceScreens::all() as $key => $screen) {
        $base = '/'.$screen::section().'/'.$screen::slug();

        Route::middleware('permission:'.$screen::viewPermission())->group(function () use ($base, $key, $screen): void {
            Route::get($base, [ResourcePageController::class, 'index'])->name($screen::routeSuffix('index'))->defaults('screen', $key);

            if ($screen::creatable()) {
                Route::get($base.'/create', [ResourcePageController::class, 'create'])->middleware('permission:'.$screen::managePermission())->name($screen::routeSuffix('create'))->defaults('screen', $key);
            }

            Route::get($base.'/{record}', [ResourcePageController::class, 'edit'])->where('record', '[A-Za-z0-9_-]+')->name($screen::routeSuffix('edit'))->defaults('screen', $key);
        });
    }
});
