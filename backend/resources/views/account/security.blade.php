@extends('layouts.account')

@section('title', __('account.security.title'))
@section('nav', 'security')

@section('page')
    <x-account.page-head :title="__('account.security.title')" :subtitle="__('account.security.subtitle')" />

    {{-- Password (PUT /api/v1/account/password): other devices are signed out. --}}
    <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-6" aria-labelledby="password-title">
        <h2 id="password-title" class="text-lg font-semibold">{{ __('account.security.password_title') }}</h2>
        <form class="mt-4 flex flex-col gap-4" data-module="account-form" data-action="{{ url('/api/v1/account/password') }}" data-method="PUT" data-reset novalidate>
            <div class="grid gap-4 md:grid-cols-3">
                <x-store.field name="current_password" id="pw-current" type="password" :label="__('account.fields.current_password')" autocomplete="current-password" required />
                <x-store.field name="password" id="pw-new" type="password" :label="__('account.fields.new_password')" :help="__('account.security.password_help')" autocomplete="new-password" required />
                <x-store.field name="password_confirmation" id="pw-confirm" type="password" :label="__('account.fields.confirm_password')" autocomplete="new-password" required />
            </div>
            <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
            <div class="alert-success" data-form-success hidden role="status"><x-store.icon name="check" class="mt-0.5 text-status-success" /><p data-form-success-text></p></div>
            <div class="flex justify-end"><button type="submit" class="btn-primary">{{ __('account.security.update_password') }}</button></div>
        </form>
    </section>

    {{-- Two-step verification (API 4a). --}}
    <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-6" aria-labelledby="twofa-title"
        data-module="two-factor" data-start-url="{{ url('/api/v1/account/security/two-factor') }}"
        data-confirm-url="{{ url('/api/v1/account/security/two-factor/confirm') }}" data-qr-url="{{ url()->localized('storefront.account.two-factor-qr') }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="twofa-title" class="text-lg font-semibold">{{ __('account.security.twofa_title') }}</h2>
                <p class="mt-1 text-sm text-content-secondary">{{ __('account.security.twofa_text') }}</p>
            </div>
            <x-account.badge :tone="$twoFactorEnabled ? 'success' : 'neutral'">{{ $twoFactorEnabled ? __('account.security.on') : __('account.security.off') }}</x-account.badge>
        </div>

        @if ($twoFactorEnabled)
            @unless ($isStaff)
                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" data-2fa-disable novalidate>
                    <x-store.field class="sm:w-72" name="current_password" id="twofa-disable-password" type="password" :label="__('account.fields.current_password')" autocomplete="current-password" required />
                    <button type="submit" class="btn-secondary">{{ __('account.security.twofa_disable') }}</button>
                    <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
                </form>
            @endunless
        @else
            <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" data-2fa-start novalidate>
                <x-store.field class="sm:w-72" name="current_password" id="twofa-password" type="password" :label="__('account.fields.current_password')" autocomplete="current-password" required />
                <button type="submit" class="btn-primary">{{ __('account.security.twofa_enable') }}</button>
                <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
            </form>

            <div class="mt-4 grid gap-4 md:grid-cols-[200px_minmax(0,1fr)]" data-2fa-setup hidden>
                <img alt="{{ __('account.security.qr_alt') }}" width="200" height="200" class="size-[200px] rounded-md bg-white p-2" data-2fa-qr>
                <div class="flex flex-col gap-3">
                    <ol class="list-decimal ps-5 text-sm text-content-secondary">
                        <li>{{ __('account.security.twofa_step1') }}</li>
                        <li>{{ __('account.security.twofa_step2') }}</li>
                    </ol>
                    <p class="text-xs text-content-muted">{{ __('account.security.setup_key') }} <code class="font-mono text-content-primary select-all" dir="ltr" data-2fa-secret></code></p>
                    <form class="flex flex-col gap-3 sm:flex-row sm:items-end" data-2fa-confirm novalidate>
                        <x-store.field class="sm:w-48" name="code" id="twofa-code" :label="__('account.security.code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" dir="ltr" required />
                        <button type="submit" class="btn-primary">{{ __('account.security.twofa_confirm') }}</button>
                        <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
                    </form>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3" data-2fa-recovery hidden>
                <div class="alert-success"><x-store.icon name="check" class="mt-0.5 text-status-success" /><p>{{ __('account.security.recovery_text') }}</p></div>
                <ul class="grid grid-cols-2 gap-2 rounded-md bg-surface-sunken p-4 font-mono text-sm sm:grid-cols-4" dir="ltr" data-2fa-codes></ul>
                <div><button type="button" class="btn-primary" data-2fa-done>{{ __('account.security.recovery_saved') }}</button></div>
            </div>
        @endif
    </section>

    {{-- Code reveal history (digital_code_accesses of this customer). --}}
    <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-6" aria-labelledby="reveals-title">
        <h2 id="reveals-title" class="text-lg font-semibold">{{ __('account.security.reveals_title') }}</h2>
        @if ($reveals === [])
            <p class="mt-3 text-sm text-content-secondary">{{ __('account.security.reveals_empty') }}</p>
        @else
            <ul class="mt-3 flex flex-col divide-y divide-line-subtle text-sm">
                @foreach ($reveals as $reveal)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                        <span>{{ $reveal['name'] }} <span class="font-mono text-content-muted" dir="ltr">{{ str_replace('*', '•', (string) $reveal['masked_code']) }}</span></span>
                        <span @class(['text-status-danger' => ! $reveal['granted'], 'text-content-secondary' => $reveal['granted']])>
                            <x-account.time :at="$reveal['at']" format="datetime" /> ·
                            {{ $reveal['granted'] ? __('account.security.reveal_ok') : __('account.security.reveal_denied') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Signed-in devices (GET / DELETE /api/v1/account/devices) and sign out everywhere else. --}}
    <section class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-6" aria-labelledby="devices-title">
        <h2 id="devices-title" class="text-lg font-semibold">{{ __('account.security.devices_title') }}</h2>
        @unless ($webSessionsListed)
            <p class="mt-1 text-xs text-content-muted">{{ __('account.security.web_sessions_hidden') }}</p>
        @endunless
        <ul class="mt-3 flex flex-col divide-y divide-line-subtle" data-module="account-action">
            @forelse ($devices as $device)
                <li class="flex items-center gap-3 py-3" data-device>
                    <x-store.icon :name="$device['type'] === 'mobile' ? 'phone' : 'monitor'" class="shrink-0 text-content-muted" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">{{ $device['device_name'] ?? ($device['user_agent'] ? \Illuminate\Support\Str::limit($device['user_agent'], 60) : __('account.security.browser')) }}</p>
                        <p class="text-xs text-content-muted">
                            {{ __('account.security.device_types.'.$device['type']) }}
                            @if ($device['ip']) · <span dir="ltr">{{ $device['ip'] }}</span>@endif
                            @if ($device['last_used_at']) · {{ __('account.security.last_active') }} <x-account.time :at="$device['last_used_at']" format="relative" />@endif
                        </p>
                    </div>
                    @if ($device['current'])
                        <x-account.badge tone="accent" :dot="false">{{ __('account.security.this_device') }}</x-account.badge>
                    @else
                        <button type="button" class="btn-ghost min-h-9 px-3 text-xs" data-action-url="{{ url('/api/v1/account/devices/'.$device['id']) }}" data-action-method="DELETE"
                            data-action-confirm="{{ __('account.security.revoke_confirm') }}" data-action-done="{{ __('account.security.revoked') }}" data-action-remove="[data-device]">{{ __('account.security.sign_out') }}</button>
                    @endif
                </li>
            @empty
                <li class="py-3 text-sm text-content-secondary">{{ __('account.security.no_devices') }}</li>
            @endforelse
        </ul>

        <details class="group mt-4 border-t border-line-subtle pt-4">
            <summary class="inline-flex cursor-pointer list-none items-center gap-2 text-sm font-medium text-status-danger"><x-store.icon name="logout" :size="16" /> {{ __('account.security.sign_out_others') }}</summary>
            <form class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end" data-module="account-form" data-action="{{ url('/api/v1/account/devices/sign-out-others') }}" data-method="POST" data-success="reload" novalidate>
                <x-store.field class="sm:w-72" name="current_password" id="others-password" type="password" :label="__('account.fields.current_password')" autocomplete="current-password" required />
                <button type="submit" class="btn-secondary">{{ __('account.security.sign_out_others_submit') }}</button>
                <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
            </form>
            <p class="mt-2 text-xs text-content-muted">{{ __('account.security.sign_out_others_text') }}</p>
        </details>
    </section>
@endsection
