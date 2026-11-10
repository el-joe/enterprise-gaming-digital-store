<?php

namespace App\Providers;

use App\Domains\Analytics\Listeners\RecordPurchaseEvent;
use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Currency\Adapters\OpenExchangeRatesProvider;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Fulfillment\Listeners\StartFulfilmentWhenPaid;
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
use App\Domains\Promotions\Services\UnavailableCheckoutCoupons;
use App\Domains\Shared\Models\MorphMap;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExchangeRateProvider::class, OpenExchangeRatesProvider::class);

        // M10: payment currency through eligibility (default deny). Coupons stay fail-closed until M11.
        $this->app->bind(PaymentCurrencyResolver::class, EligiblePaymentCurrencyResolver::class);
        $this->app->bind(CheckoutCoupons::class, UnavailableCheckoutCoupons::class);
        $this->app->singleton(PaymentGatewayRegistry::class);
        $this->app->bind(GeoIpResolver::class, static fn (): GeoIpResolver => config('payments.geoip.driver') === 'header'
            ? new HeaderGeoIpResolver((string) config('payments.geoip.header'))
            : new NullGeoIpResolver);

        $this->app->singleton(SupplierAdapterRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Relation::enforceMorphMap(MorphMap::aliases());

        $this->configureRateLimiting();

        Event::listen(OrderStatusChanged::class, StartFulfilmentWhenPaid::class);
        Event::listen(OrderStatusChanged::class, SendFulfilmentNotifications::class);
        Event::listen(OrderStatusChanged::class, RefundUndeliveredUnits::class);
        Event::listen(OrderStatusChanged::class, RecognizeOrderFinancials::class);
        Event::listen(OrderStatusChanged::class, RecordPurchaseEvent::class);
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
            $identifier = $request->session()->get('auth.two_factor.pending.user_id') ?? $request->ip();

            return Limit::perMinutes(10, 5)->by('otp|'.$identifier);
        });

        RateLimiter::for('storefront', static fn (Request $request): Limit => Limit::perMinute(120)->by('storefront|'.$request->ip()));
        RateLimiter::for('search', static fn (Request $request): Limit => Limit::perMinute(60)->by('search|'.$request->ip()));
        RateLimiter::for('checkout', static fn (Request $request): array => [
            Limit::perMinute(20)->by('checkout|session|'.($request->hasSession() ? $request->session()->getId() : $request->ip())),
            Limit::perMinute(60)->by('checkout|ip|'.$request->ip()),
        ]);
        RateLimiter::for('code-reveal', static function (Request $request): Limit {
            $order = $request->route('order');
            $viewer = $request->user()?->getAuthIdentifier() ?? 'guest:'.($order instanceof Order ? $order->public_id : (string) $order);

            return Limit::perHour((int) config('fulfillment.reveal_per_hour'))->by('code-reveal|'.$viewer);
        });
        RateLimiter::for('webhooks', static fn (Request $request): Limit => Limit::perMinute(600)->by('webhooks|'.$request->ip()));
        RateLimiter::for('admin', static fn (Request $request): Limit => Limit::perMinute(300)->by('admin|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }
}
