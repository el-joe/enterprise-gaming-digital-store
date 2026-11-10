{{--
    Error pages without the storefront chrome (500, 503, 419, 429, 403, 401): no database access,
    no session, so they render even when the application is failing.
--}}
@php($locale = \App\Http\Support\LocalizedRoutes::localeOfPath(request()->path()))
@php(app()->setLocale($locale))
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') | {{ config('brand.name') }}</title>
    <x-brand.head-icons />
    @if (is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="flex min-h-screen flex-col items-center justify-center gap-5 bg-surface-canvas px-4 text-center text-content-primary">
    <a href="{{ url('/') }}"><x-brand.logo :height="36" eager /></a>
    <p class="font-display text-[56px] leading-[60px] font-extrabold text-ember-400">@yield('code')</p>
    <h1 class="font-display text-2xl font-bold">@yield('message')</h1>
    <p class="max-w-md text-sm text-content-secondary">{{ __('storefront.errors.generic_text') }}</p>
    <a href="{{ url('/') }}" class="btn-primary">{{ __('storefront.common.back_to_store') }}</a>
</body>
</html>
