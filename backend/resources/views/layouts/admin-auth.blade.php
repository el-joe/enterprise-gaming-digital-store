{{-- Admin sign-in and two-step pages (M14): a centred card on the light canvas, the logo on its dark chip. --}}
@php
    $config = ['locale' => $admin['locale'], 'csrf' => csrf_token(), 'routes' => ['login' => route('admin.login'), 'logout' => url('/logout')], 'i18n' => trans('admin.js') + trans('storefront.js')];
@endphp
<!DOCTYPE html>
<html lang="{{ $admin['locale'] }}" dir="{{ $admin['dir'] }}" data-theme="{{ config('admin.theme') === 'light' ? 'admin-light' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') | {{ __('admin.title') }}</title>
    <x-brand.head-icons />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-surface-canvas px-4 py-10 text-content-primary">
    <main id="main" class="w-full max-w-[420px]">
        <div class="mb-6 flex items-center justify-between">
            <x-brand.logo :height="32" :surface="config('admin.theme') === 'light' ? 'light' : 'dark'" eager />
            <form method="POST" action="{{ route('admin.locale') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $admin['locale'] === 'ar' ? 'en' : 'ar' }}">
                <button type="submit" class="admin-btn px-2.5" lang="{{ $admin['locale'] === 'ar' ? 'en' : 'ar' }}">{{ $admin['locale'] === 'ar' ? 'English' : 'العربية' }}</button>
            </form>
        </div>
        <div class="admin-card p-6 md:p-8">
            @yield('content')
        </div>
        <p class="mt-4 text-center text-xs text-content-muted">{{ __('admin.auth.staff_only') }}</p>
    </main>
    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4" aria-live="polite" role="status" data-toast-region></div>
    <script type="application/json" id="store-config" nonce="{{ Vite::cspNonce() }}">@json($config)</script>
</body>
</html>
