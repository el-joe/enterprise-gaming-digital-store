<?php

namespace App\Providers;

use App\Domains\Affiliates\Listeners\TrackAffiliateCommission;
use App\Domains\Analytics\Listeners\RecordPurchaseEvent;
use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Listeners\RecordScheduledTaskHeartbeat;
use App\Domains\Automation\Services\ScheduledTaskHeartbeat;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Currency\Adapters\CurrencyApiProvider;
use App\Domains\Currency\Adapters\OpenExchangeRatesProvider;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Fulfillment\Listeners\StartFulfilmentWhenPaid;
use App\Domains\Identity\Contracts\DevicePushTokens;
use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Notifications\Channels\PushChannel;
use App\Domains\Notifications\Repositories\PushTokenRepository;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Listeners\RecognizeOrderFinancials;
use App\Domains\Orders\Listeners\SendFulfilmentNotifications;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use App\Domains\Payments\Listeners\RefundUndeliveredUnits;
use App\Domains\Payments\Services\EligiblePaymentCurrencyResolver;
use App\Domains\Payments\Services\HeaderGeoIpResolver;
use App\Domains\Payments\Services\NullGeoIpResolver;
use App\Domains\Payments\Services\PaymentGatewayRegistry;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\Listeners\RedeemCouponWhenPaid;
use App\Domains\Promotions\Services\DatabaseCheckoutCoupons;
use App\Domains\Shared\Models\MorphMap;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use App\Domains\Support\Listeners\CompleteRefundRequests;
use App\Policies\OrderPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Exchange rate source from the admin setting currency.rates_provider (default: currency_api).
        $this->app->bind(ExchangeRateProvider::class, static fn ($app): ExchangeRateProvider => config('services.exchange_rates.provider') === 'open_exchange_rates'
            ? $app->make(OpenExchangeRatesProvider::class)
            : $app->make(CurrencyApiProvider::class));

        // M10: payment currency through eligibility (default deny). M11: real coupon validity and
        // limits; UnavailableCheckoutCoupons remains the fail-closed implementation of the contract.
        $this->app->bind(PaymentCurrencyResolver::class, EligiblePaymentCurrencyResolver::class);
        $this->app->bind(CheckoutCoupons::class, DatabaseCheckoutCoupons::class);
        // M-A18 (ADR-042): Identity drops push tokens with revoked sign-ins through this contract.
        $this->app->bind(DevicePushTokens::class, PushTokenRepository::class);
        $this->app->singleton(PaymentGatewayRegistry::class);
        $this->app->bind(GeoIpResolver::class, static fn (): GeoIpResolver => config('payments.geoip.driver') === 'header'
            ? new HeaderGeoIpResolver((string) config('payments.geoip.header'))
            : new NullGeoIpResolver);

        $this->app->singleton(SupplierAdapterRegistry::class);

        // M17: holds the open heartbeat of each running scheduled task for the process.
        $this->app->singleton(ScheduledTaskHeartbeat::class);

        // M-A3: translations preloaded for the current request's storefront resources.
        $this->app->scoped(ContentTranslator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Relation::enforceMorphMap(MorphMap::aliases());

        $this->configureRateLimiting();

        // M-A5: customer order ownership (another customer's order is 404).
        Gate::policy(Order::class, OrderPolicy::class);

        Event::listen(OrderStatusChanged::class, StartFulfilmentWhenPaid::class);
        Event::listen(OrderStatusChanged::class, SendFulfilmentNotifications::class);
        Event::listen(OrderStatusChanged::class, RefundUndeliveredUnits::class);
        Event::listen(OrderStatusChanged::class, RecognizeOrderFinancials::class);
        Event::listen(OrderStatusChanged::class, RecordPurchaseEvent::class);
        Event::listen(OrderStatusChanged::class, RedeemCouponWhenPaid::class);
        Event::listen(OrderStatusChanged::class, TrackAffiliateCommission::class);
        Event::listen(OrderStatusChanged::class, CompleteRefundRequests::class);
        // M-A18 (ADR-042): the `push` notification channel (FCM), a no-op while push.enabled is off.
        Notification::extend('push', static fn (Application $app): PushChannel => $app->make(PushChannel::class));

        Event::listen([ScheduledTaskStarting::class, ScheduledTaskFinished::class, ScheduledTaskFailed::class], RecordScheduledTaskHeartbeat::class);
    }

    /**
     * The client a per-session limiter counts: the web session, else (stateless clients, M-A3)
     * the signed-in user, the bearer token, the guest cart token or the guest order token, else
     * the IP. Token values are hashed into the key.
     */
    private static function checkoutClientKey(Request $request): string
    {
        if ($request->hasSession()) {
            return $request->session()->getId();
        }

        // The limiter runs before route authentication, so a bearer token is resolved here.
        $userId = $request->bearerToken() !== null ? Auth::guard('sanctum')->user()?->getAuthIdentifier() : null;

        if ($userId !== null) {
            return 'user:'.$userId;
        }

        foreach (['bearer' => $request->bearerToken(), 'cart' => $request->headers->get('X-Cart-Token'), 'guest-order' => $request->headers->get('X-Guest-Order-Token')] as $kind => $value) {
            if (is_string($value) && $value !== '') {
                return $kind.':'.hash('sha256', $value);
            }
        }

        return 'ip:'.$request->ip();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('auth', function (Request $request): Limit {
            $email = is_string($request->input('email')) ? strtolower($request->input('email')) : '';

            return Limit::perMinute(5)
                ->by($email.'|'.$request->ip())
                ->response(function (Request $request, array $headers) use ($email): never {
                    app(SecurityEventRecorder::class)->record(
                        'auth.throttled',
                        SecurityEventSeverity::Warning,
                        $request->ip(),
                        context: ['path' => $request->path(), 'email_hash' => hash('sha256', $email)],
                    );

                    throw new ThrottleRequestsException('Too Many Attempts.', null, $headers);
                });
        });

        RateLimiter::for('otp', static function (Request $request): Limit {
            // Web: the pending user in the session. Mobile (no session): the challenge token.
            $challenge = $request->input('challenge_token');
            $identifier = $request->hasSession()
                ? $request->session()->get('auth.two_factor.pending.user_id')
                : (is_string($challenge) && $challenge !== '' ? 'mobile|'.hash('sha256', $challenge) : null);
            $identifier ??= $request->ip();

            return Limit::perMinutes(10, 5)->by('otp|'.$identifier);
        });

        RateLimiter::for('mobile-refresh', static function (Request $request): array {
            $token = $request->input('refresh_token');

            return [
                Limit::perMinute((int) config('mobile.auth.refresh_per_minute_per_token'))->by('mobile-refresh|token|'.hash('sha256', is_string($token) ? $token : '')),
                Limit::perMinute((int) config('mobile.auth.refresh_per_minute_per_ip'))->by('mobile-refresh|ip|'.$request->ip()),
            ];
        });
        RateLimiter::for('mobile-biometric', static fn (Request $request): array => [
            Limit::perMinute((int) config('mobile.biometric.per_minute'))->by('mobile-biometric|user|'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
            Limit::perMinute(60)->by('mobile-biometric|ip|'.$request->ip()),
        ]);
        RateLimiter::for('mobile-push', static fn (Request $request): array => [
            Limit::perMinute((int) config('push.register_per_minute'))->by('mobile-push|user|'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
            Limit::perMinute((int) config('push.register_per_minute_per_ip'))->by('mobile-push|ip|'.$request->ip()),
        ]);
        RateLimiter::for('storefront', static fn (Request $request): Limit => Limit::perMinute(120)->by('storefront|'.$request->ip()));
        RateLimiter::for('search', static fn (Request $request): Limit => Limit::perMinute(60)->by('search|'.$request->ip()));
        RateLimiter::for('checkout', static fn (Request $request): array => [
            Limit::perMinute(20)->by('checkout|session|'.self::checkoutClientKey($request)),
            Limit::perMinute(60)->by('checkout|ip|'.$request->ip()),
        ]);
        RateLimiter::for('code-reveal', static function (Request $request): Limit {
            $order = $request->route('order');
            $viewer = $request->user()?->getAuthIdentifier() ?? 'guest:'.($order instanceof Order ? $order->public_id : (string) $order);

            return Limit::perHour((int) config('fulfillment.reveal_per_hour'))->by('code-reveal|'.$viewer);
        });
        RateLimiter::for('webhooks', static fn (Request $request): Limit => Limit::perMinute(600)->by('webhooks|'.$request->ip()));
        RateLimiter::for('account', static fn (Request $request): Limit => Limit::perMinute(60)->by('account|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
        RateLimiter::for('mcp', static function (Request $request): Limit {
            $caller = $request->attributes->get(McpCaller::REQUEST_ATTRIBUTE);

            return $caller instanceof McpCaller
                ? Limit::perMinute(max(1, $caller->account->rate_limit_per_minute))->by('mcp|account|'.$caller->account->id)
                : Limit::perMinute(30)->by('mcp|ip|'.$request->ip());
        });
        RateLimiter::for('automation', static function (Request $request): Limit {
            $caller = $request->attributes->get(AutomationCaller::REQUEST_ATTRIBUTE);

            return $caller instanceof AutomationCaller
                ? Limit::perMinute(max(1, $caller->account->rate_limit_per_minute))->by('automation|account|'.$caller->account->id)
                : Limit::perMinute(30)->by('automation|ip|'.$request->ip());
        });
        RateLimiter::for('admin', static fn (Request $request): Limit => Limit::perMinute(300)->by('admin|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
        // Loading the staff sign-in and two-step pages (M19 defect D2). The attempts themselves are
        // limited where they are submitted: POST /login by `auth` (5 per minute per email and IP) and
        // POST /two-factor-challenge by `otp`. A page load carries no email, so `auth` here made every
        // staff member behind one office IP share 5 page loads a minute.
        RateLimiter::for('admin-auth-pages', static fn (Request $request): Limit => Limit::perMinute(60)->by('admin-auth-pages|'.$request->ip()));
    }
}
