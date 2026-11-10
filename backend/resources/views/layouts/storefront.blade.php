{{--
    Storefront layout (M12). Pages set: title, description, robots (default index,follow),
    canonical (default: the URL without its query string) and optionally `header`: `minimal`
    (checkout: logo and secure badge, no footer) or `none` (auth split screen).
    Script data travels in the nonced JSON block below; no inline script or style is used.
--}}
@php
    $title = trim($__env->yieldContent('title'));
    $fullTitle = $title === '' ? config('brand.name').' — '.__('storefront.meta.tagline') : $title.' | '.config('brand.name');
    $description = trim($__env->yieldContent('description')) ?: __('storefront.meta.description');
    $robots = trim($__env->yieldContent('robots')) ?: 'index,follow';
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
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
            'platform' => url()->localized('storefront.platforms.show', ['slug' => '__slug__']),
            'category' => url()->localized('storefront.category', ['slug' => '__slug__']),
            'cartPage' => url()->localized('storefront.cart'),
            'checkoutPage' => url()->localized('storefront.checkout'),
            'login' => url()->localized('storefront.login'),
            'account' => url()->localized('storefront.account'),
            'logout' => url('/logout'),
            'home' => url()->localized('storefront.home'),
        ],
        'i18n' => trans('storefront.js'),
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
    @if (str_starts_with($robots, 'index'))
        <link rel="canonical" href="{{ $canonical }}">
        @foreach ($storefront['languages'] as $language)
            <link rel="alternate" hreflang="{{ $language['code'] }}" href="{{ $language['url'] }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ collect($storefront['languages'])->firstWhere('code', config('app.fallback_locale'))['url'] ?? url('/') }}">
    @endif
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

    <script type="application/json" id="store-config" nonce="{{ Vite::cspNonce() }}">@json($config)</script>
    @stack('data')
</body>
</html>
