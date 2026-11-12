{{--
    Storefront layout (M12). Pages set: title, description, robots (default index,follow),
    canonical (default: the URL without its query string) and optionally
    `header`: `minimal` (checkout: logo and secure badge, no footer) or `none` (auth split screen).
    Script data travels in the nonced JSON blocks below; no inline script or style is used.
    M18: hreflang alternates follow the canonical URL (LocalizedRoutes::alternates); JSON-LD comes
    from PageSchema; analytics events from PageAnalytics, and the tag loads only after consent.
--}}
@php
    $title = trim($__env->yieldContent('title'));
    $fullTitle = $title === '' ? config('brand.name').' — '.__('storefront.meta.tagline') : $title.' | '.config('brand.name');
    $description = trim($__env->yieldContent('description')) ?: __('storefront.meta.description');
    $robots = trim($__env->yieldContent('robots')) ?: 'index,follow';
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $indexable = str_starts_with($robots, 'index');
    $alternates = $indexable ? \App\Http\Support\LocalizedRoutes::alternates(request(), $canonical) : [];
    $analytics = app(\App\Domains\Analytics\Services\AnalyticsSettings::class);
    $analyticsConfig = $analytics->browserConfig();
    $siteVerification = $analytics->searchConsoleVerification();
    $headerMode = trim($__env->yieldContent('header'));
    $minimal = $headerMode === 'minimal';
    $config = [
        'locale' => $storefront['locale'],
        'currency' => $storefront['currency'],
        'csrf' => csrf_token(),
        'signedIn' => $storefront['user'] !== null,
        'routes' => [
            'currency' => url('/api/v1/storefront/currency'),
            'suggest' => url('/api/v1/storefront/search/suggest'),
            'popular' => url('/api/v1/storefront/search/popular'),
            'search' => url()->localized('storefront.search'),
            'product' => url()->localized('storefront.products.show', ['slug' => '__slug__']),
            'platform' => url()->localized('storefront.brand', ['slug' => '__slug__']),
            'products' => url()->localized('storefront.products.index'),
            'facets' => url('/api/v1/storefront/catalog/facets'),
            'category' => url()->localized('storefront.category', ['slug' => '__slug__']),
            'cartPage' => url()->localized('storefront.cart'),
            'checkoutPage' => url()->localized('storefront.checkout'),
            'login' => url()->localized('storefront.login'),
            'account' => url()->localized('storefront.account'),
            'logout' => url('/logout'),
            'wishlist' => url('/api/v1/account/wishlist'),
            'home' => url()->localized('storefront.home'),
        ],
        'i18n' => trans('storefront.js') + trans('account.js'),
        'analytics' => $analyticsConfig,
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $storefront['locale'] }}" dir="{{ $storefront['dir'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    @if ($indexable)
        <link rel="canonical" href="{{ $canonical }}">
        @foreach ($alternates as $code => $href)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}">
        @endforeach
        @if (isset($alternates[config('app.fallback_locale')]))
            <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('app.fallback_locale')] }}">
        @endif
    @endif
    @if ($siteVerification)<meta name="google-site-verification" content="{{ $siteVerification }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $storefront['locale'] === 'ar' ? 'ar_EG' : 'en_US' }}">
    <meta name="twitter:card" content="summary_large_image">
    <x-brand.head-icons />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only z-50 rounded-md bg-accent-primary px-4 py-2 font-semibold text-content-on-accent focus:not-sr-only focus:fixed focus:start-4 focus:top-4">{{ __('storefront.common.skip') }}</a>

    @unless ($headerMode === 'none')
        <x-store.header :storefront="$storefront" :minimal="$minimal" />
    @endunless

    <main id="main" class="flex-1" tabindex="-1">
        @yield('content')
    </main>

    @unless ($minimal || $headerMode === 'none')
        <x-store.footer :storefront="$storefront" />
    @endunless

    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4" aria-live="polite" role="status" data-toast-region></div>

    @unless ($minimal || $headerMode === 'none')
        @include('storefront.partials.quick-add-dialog')
    @endunless

    @if ($analyticsConfig)
        @include('storefront.partials.consent')
    @endif

    <script type="application/json" id="store-config" nonce="{{ Vite::cspNonce() }}">@json($config)</script>
    @if ($analyticsConfig)
        <script type="application/json" id="analytics-events" nonce="{{ Vite::cspNonce() }}">@json(app(\App\Domains\Analytics\Services\PageAnalytics::class)->events())</script>
    @endif
    <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">{!! app(\App\Domains\Seo\StructuredData\PageSchema::class)->toJson($storefront['locale']) !!}</script>
    @stack('data')
</body>
</html>
