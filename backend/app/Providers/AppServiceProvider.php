<?php

namespace App\Providers;

use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Currency\Adapters\OpenExchangeRatesProvider;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Shared\Models\MorphMap;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Relation::enforceMorphMap(MorphMap::aliases());

        $this->configureRateLimiting();
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
        RateLimiter::for('admin', static fn (Request $request): Limit => Limit::perMinute(300)->by('admin|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }
}
