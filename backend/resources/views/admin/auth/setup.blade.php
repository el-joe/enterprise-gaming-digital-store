@extends('layouts.admin-auth')

@section('title', __('admin.auth.setup_title'))

@section('content')
    <h1 class="font-display text-2xl leading-8 font-bold">{{ __('admin.auth.setup_title') }}</h1>
    <p class="mt-1 text-sm text-content-secondary">{{ __('admin.auth.setup_intro') }}</p>

    <div class="mt-6" data-module="two-factor" data-start-url="{{ url('/api/v1/account/security/two-factor') }}"
        data-confirm-url="{{ url('/api/v1/account/security/two-factor/confirm') }}" data-qr-url="{{ route('admin.two-factor.qr') }}">
        <form class="flex flex-col gap-4" data-2fa-start novalidate>
            <x-admin.field name="current_password" type="password" :label="__('admin.auth.password')" autocomplete="current-password" required />
            <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5 text-status-danger" /><p data-form-error-text></p></div>
            <button type="submit" class="admin-btn-primary min-h-11 w-full">{{ __('admin.auth.setup_start') }}</button>
        </form>

        <div class="flex flex-col gap-4" data-2fa-setup hidden>
            <p class="text-sm text-content-secondary">{{ __('admin.auth.setup_scan') }}</p>
            <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="{{ __('admin.auth.setup_qr') }}" width="200" height="200" class="mx-auto size-[200px] rounded-md border border-line-subtle bg-white p-2" data-2fa-qr>
            <p class="text-xs text-content-muted">{{ __('admin.auth.setup_key') }} <code class="font-mono text-content-primary" dir="ltr" data-2fa-secret></code></p>
            <form class="flex flex-col gap-4" data-2fa-confirm novalidate>
                <x-admin.field name="code" :label="__('admin.auth.code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" dir="ltr" />
                <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5 text-status-danger" /><p data-form-error-text></p></div>
                <button type="submit" class="admin-btn-primary min-h-11 w-full">{{ __('admin.auth.setup_confirm') }}</button>
            </form>
        </div>

        <div class="flex flex-col gap-4" data-2fa-recovery hidden>
            <div class="alert-warning"><x-store.icon name="alert" class="mt-0.5 text-status-warning" /><p>{{ __('admin.auth.recovery_once') }}</p></div>
            <ul class="grid grid-cols-2 gap-2 font-mono text-sm" dir="ltr" data-2fa-codes></ul>
            <button type="button" class="admin-btn-primary min-h-11 w-full" data-2fa-done>{{ __('admin.auth.setup_done') }}</button>
        </div>
    </div>
@endsection
