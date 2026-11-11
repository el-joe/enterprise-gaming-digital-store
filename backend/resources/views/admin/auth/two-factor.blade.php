@extends('layouts.admin-auth')

@section('title', __('admin.auth.two_factor_title'))

@section('content')
    <h1 class="font-display text-2xl leading-8 font-bold">{{ __('admin.auth.two_factor_title') }}</h1>
    <p class="mt-1 text-sm text-content-secondary">{{ __('admin.auth.two_factor_intro') }}</p>
    <form class="mt-6 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/two-factor-challenge') }}" data-kind="two-factor" data-redirect="{{ route('admin.home') }}" novalidate>
        <div data-code-field>
            <x-admin.field name="code" :label="__('admin.auth.code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" dir="ltr" />
        </div>
        <div data-recovery-field hidden>
            <x-admin.field name="recovery_code" :label="__('admin.auth.recovery_code')" autocomplete="off" dir="ltr" />
        </div>
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5 text-status-danger" /><p data-form-error-text></p></div>
        <button type="submit" class="admin-btn-primary min-h-11 w-full">{{ __('admin.auth.verify') }}</button>
        <button type="button" class="text-sm text-accent-text underline-offset-2 hover:underline" data-toggle-recovery>{{ __('admin.auth.use_recovery') }}</button>
    </form>
@endsection
