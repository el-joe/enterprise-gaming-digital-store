{{--
    Admin layout (M14, Figma "Admin shell", Theme mode "Admin Light"): sidebar (full from lg,
    icon rail at md and when collapsed, drawer below md), top bar, breadcrumbs, user menu and
    language switch. Pages set `title`, `heading`, optional `breadcrumbs` and `actions`, and
    `content`. Script data travels in the nonced JSON block; no inline script or style.
--}}
@php
    $title = trim($__env->yieldContent('title'));
    $config = [
        'locale' => $admin['locale'],
        'csrf' => csrf_token(),
        'routes' => [
            'logout' => url('/logout'),
            'login' => route('admin.login'),
        ],
        'i18n' => trans('admin.js') + trans('storefront.js'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $admin['locale'] }}" dir="{{ $admin['dir'] }}" data-theme="{{ config('admin.theme') === 'light' ? 'admin-light' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title === '' ? __('admin.title') : $title.' | '.__('admin.title') }}</title>
    <x-brand.head-icons />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-surface-canvas text-content-primary">
    <a href="#main" class="sr-only z-50 rounded-sm bg-accent-primary px-4 py-2 font-semibold text-content-on-accent focus:not-sr-only focus:fixed focus:start-4 focus:top-4">{{ __('admin.skip') }}</a>

    <div class="flex min-h-screen" data-module="shell">
        <aside class="admin-sidebar fixed inset-y-0 start-0 z-40 flex w-64 flex-col border-e border-line-subtle bg-surface-base transition-transform duration-200 max-md:-translate-x-full max-md:rtl:translate-x-full md:sticky md:top-0 md:h-screen md:w-[76px] lg:w-60" data-admin-sidebar aria-label="{{ __('admin.nav.label') }}">
            <div class="flex h-16 shrink-0 items-center px-4">
                <a href="{{ route('admin.home') }}" class="flex items-center" aria-label="{{ __('admin.nav.home') }}">
                    <span class="admin-logo-full inline-flex md:hidden lg:inline-flex"><x-brand.logo :height="28" :surface="config('admin.theme') === 'light' ? 'light' : 'dark'" eager /></span>
                    <span class="admin-logo-mark hidden md:inline-flex lg:hidden"><x-brand.logo variant="mark" :height="32" :surface="config('admin.theme') === 'light' ? 'light' : 'dark'" eager /></span>
                </a>
            </div>
            <nav class="flex-1 overflow-y-auto px-3 py-3">
                <ul class="flex flex-col gap-1">
                    @foreach ($admin['navigation'] as $item)
                        @php($current = $admin['section'] === $item['key'] || ($item['key'] === 'dashboard' && $admin['section'] === 'dashboard'))
                        <li>
                            <a href="{{ $item['url'] }}" title="{{ __('admin.nav.'.$item['key']) }}" @class([
                                'admin-nav-link relative flex min-h-10 items-center gap-3 rounded-md px-3 text-sm font-medium transition md:justify-center md:px-0 lg:justify-start lg:px-3',
                                'bg-surface-sunken text-content-primary before:absolute before:inset-y-2 before:start-0 before:w-1 before:rounded-full before:bg-accent-primary' => $current,
                                'text-content-secondary hover:bg-surface-sunken hover:text-content-primary' => ! $current,
                            ]) @if ($current) aria-current="page" @endif>
                                <x-store.icon :name="$item['icon']" />
                                <span class="admin-nav-label md:sr-only lg:not-sr-only">{{ __('admin.nav.'.$item['key']) }}</span>
                                @if (($item['badge'] ?? 0) > 0)
                                    <span class="admin-nav-badge ms-auto rounded-full bg-surface-sunken px-2 text-xs leading-5 font-semibold text-content-secondary md:hidden lg:inline" aria-label="{{ trans_choice('admin.nav.attention', $item['badge'], ['count' => $item['badge']]) }}">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="border-t border-line-subtle p-3">
                <button type="button" class="admin-btn hidden w-full lg:inline-flex" data-rail-toggle aria-expanded="true">
                    <x-store.icon name="panel" :size="18" />
                    <span class="admin-nav-label">{{ __('admin.nav.collapse') }}</span>
                </button>
            </div>
        </aside>
        <div class="fixed inset-0 z-30 bg-ink-950/40 md:hidden" data-drawer-scrim hidden></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-line-subtle bg-surface-base/95 px-4 backdrop-blur md:px-6">
                <button type="button" class="admin-btn size-9 px-0 md:hidden" data-drawer-toggle aria-expanded="false" aria-label="{{ __('admin.nav.open') }}">
                    <x-store.icon name="menu" :size="18" />
                </button>
                <h1 class="min-w-0 flex-1 truncate font-display text-xl leading-7 font-bold md:text-[22px]">@yield('heading')</h1>

                @if (collect($admin['navigation'])->contains('key', 'orders'))
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="relative hidden w-72 md:block" role="search">
                        <x-store.icon name="search" :size="16" class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-content-muted" />
                        <label for="admin-search" class="sr-only">{{ __('admin.top.search_label') }}</label>
                        <input id="admin-search" type="search" name="q" maxlength="100" value="{{ request()->routeIs('admin.orders.index') ? request('q') : '' }}" placeholder="{{ __('admin.top.search') }}" class="admin-input bg-surface-sunken ps-9">
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.locale') }}" class="shrink-0">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $admin['locale'] === 'ar' ? 'en' : 'ar' }}">
                    <button type="submit" class="admin-btn px-2.5" lang="{{ $admin['locale'] === 'ar' ? 'en' : 'ar' }}">{{ $admin['locale'] === 'ar' ? 'English' : 'العربية' }}</button>
                </form>

                @if ($admin['user'])
                    <div class="relative shrink-0" data-module="popover">
                        <button type="button" class="flex size-9 items-center justify-center rounded-md bg-status-info-tint text-xs font-semibold text-accent-ultra" data-popover-toggle aria-expanded="false" aria-label="{{ __('admin.top.user_menu') }}">{{ $admin['user']['initials'] }}</button>
                        <div class="admin-card absolute end-0 top-11 z-30 w-64 p-2 shadow-raised" data-popover-panel hidden>
                            <div class="border-b border-line-subtle px-2 pt-1 pb-3">
                                <p class="truncate text-sm font-semibold">{{ $admin['user']['name'] }}</p>
                                <p class="truncate text-xs text-content-muted">{{ $admin['user']['email'] }}</p>
                                @if ($admin['user']['roles'] !== [])
                                    <p class="mt-1 text-xs text-content-secondary">{{ implode(', ', $admin['user']['roles']) }}</p>
                                @endif
                            </div>
                            <button type="button" class="mt-1 flex w-full items-center gap-2 rounded-sm px-2 py-2 text-sm text-content-primary hover:bg-surface-sunken" data-logout>
                                <x-store.icon name="logout" :size="16" /> {{ __('admin.top.sign_out') }}
                            </button>
                        </div>
                    </div>
                @endif
            </header>

            <main id="main" tabindex="-1" class="flex-1 px-4 py-5 md:px-6 md:py-6 lg:px-8">
                @hasSection('breadcrumbs')
                    <nav aria-label="{{ __('admin.breadcrumbs') }}" class="mb-4 text-sm text-content-muted">
                        <ol class="flex flex-wrap items-center gap-1.5">@yield('breadcrumbs')</ol>
                    </nav>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <dialog class="m-auto w-[min(440px,calc(100vw-2rem))] rounded-xl border border-line-subtle bg-surface-raised p-6 text-content-primary shadow-overlay backdrop:bg-ink-950/50" data-confirm-dialog aria-labelledby="confirm-title">
        <div class="flex flex-col items-center gap-3 text-center">
            <span class="flex size-12 items-center justify-center rounded-full" data-confirm-icon><x-store.icon name="alert" /></span>
            <h2 id="confirm-title" class="font-display text-xl font-bold" data-confirm-title></h2>
            <p class="text-sm text-content-secondary" data-confirm-text></p>
        </div>
        <div class="mt-4" data-confirm-reason hidden>
            <label for="confirm-reason" class="field-label">{{ __('admin.js.reason') }}</label>
            <textarea id="confirm-reason" rows="3" maxlength="1000" class="admin-input py-2" aria-describedby="confirm-reason-error"></textarea>
            <p id="confirm-reason-error" class="field-error" data-confirm-reason-error hidden></p>
            <p class="field-help">{{ __('admin.common.audited') }}</p>
        </div>
        <div class="mt-6 flex justify-center gap-3">
            <button type="button" class="admin-btn min-h-10 px-5" data-confirm-cancel>{{ __('admin.common.cancel') }}</button>
            <button type="button" class="admin-btn-primary min-h-10 px-5" data-confirm-ok></button>
        </div>
    </dialog>

    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4" aria-live="polite" role="status" data-toast-region></div>
    <script type="application/json" id="store-config" nonce="{{ Vite::cspNonce() }}">@json($config)</script>
</body>
</html>
