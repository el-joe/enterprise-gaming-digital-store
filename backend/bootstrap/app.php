<?php

use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Exceptions\JsonExceptionRenderer;
use App\Http\Middleware\Account\EnsureAccountActive;
use App\Http\Middleware\Admin\EnforceIdleTimeout;
use App\Http\Middleware\Admin\EnsureStaff;
use App\Http\Middleware\Admin\EnsureTwoFactor;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\Automation\AuthenticateAutomationRequest;
use App\Http\Middleware\Automation\RecordAutomationCall;
use App\Http\Middleware\Automation\RequireAutomationScope;
use App\Http\Middleware\Mcp\AuthenticateMcpToken;
use App\Http\Middleware\Mcp\RecordMcpCall;
use App\Http\Middleware\Mcp\RequireMcpScope;
use App\Http\Middleware\Mobile\AuthenticateOptionalCustomerToken;
use App\Http\Middleware\Mobile\EnsureCustomerToken;
use App\Http\Middleware\SetLocaleFromRequest;
use App\Http\Middleware\Storefront\PublicCacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::middleware('account')->prefix('api/v1/account')->name('account.')->group(base_path('routes/account.php'));
            Route::middleware(['api', 'mobile.account'])->prefix('api/v1/mobile/account')->name('mobile.account.')->group(base_path('routes/account.php'));
            Route::middleware('admin')->prefix('admin/api')->name('admin.api.')->group(base_path('routes/admin.php'));
            Route::middleware(['web', 'locale'])->prefix('api/v1')->name('api.v1.')->group(base_path('routes/checkout.php'));
            Route::middleware('web')->prefix('api/v1')->name('api.v1.')->group(base_path('routes/affiliates.php'));
            Route::middleware(['api', AuthenticateOptionalCustomerToken::class])->prefix('api/v1/mobile')->name('mobile.')->group(base_path('routes/checkout.php'));
            Route::middleware('mcp')->prefix('api/v1/mcp')->name('mcp.')->group(base_path('routes/mcp.php'));
            Route::middleware('automation')->prefix('api/v1/automation')->name('automation.')->group(base_path('routes/automation.php'));
            Route::middleware('api')->prefix('api/v1/mobile')->name('mobile.')->group(base_path('routes/mobile.php'));
            Route::middleware('api')->prefix('api/v1/mobile')->name('mobile.')->group(base_path('routes/storefront.php'));
            Route::middleware('api')->prefix('api/v1')->name('api.v1.')->group(base_path('routes/storefront.php'));
            Route::middleware('api')->prefix('api/v1')->name('api.v1.')->group(base_path('routes/api.php'));
            Route::prefix('webhooks')->name('webhooks.')->group(base_path('routes/webhooks.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(AssignRequestId::class);
        $middleware->encryptCookies(except: ['currency']);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'account.active' => EnsureAccountActive::class,
            'staff' => EnsureStaff::class,
            'two_factor' => EnsureTwoFactor::class,
            'admin.idle' => EnforceIdleTimeout::class,
            'mcp.scope' => RequireMcpScope::class,
            'automation.scope' => RequireAutomationScope::class,
            'locale' => SetLocaleFromRequest::class,
            'cache.public' => PublicCacheHeaders::class,
        ]);

        $middleware->appendToGroup('api', SetLocaleFromRequest::class);

        $middleware->group('account', ['web', 'auth', 'account.active', 'locale']);
        $middleware->group('admin', ['web', 'auth', 'staff', 'two_factor', 'admin.idle', 'throttle:admin']);
        $middleware->group('mcp', [RecordMcpCall::class, AuthenticateMcpToken::class, 'throttle:mcp']);
        $middleware->group('mobile.customer', ['auth:sanctum', EnsureCustomerToken::class, 'account.active', 'throttle:account']);
        // M-A3: the account routes for bearer clients; routes/account.php sets its own limiters.
        $middleware->group('mobile.account', ['auth:sanctum', EnsureCustomerToken::class, 'account.active']);
        $middleware->group('automation', [RecordAutomationCall::class, AuthenticateAutomationRequest::class, 'throttle:automation']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'current_password', 'code', 'recovery_code']);
        $exceptions->dontReport([ApplicationException::class]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => JsonExceptionRenderer::appliesTo($request),
        );

        $exceptions->render(new JsonExceptionRenderer);
    })->create();
