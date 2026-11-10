@extends('layouts.auth')

@section('title', __('storefront.auth.two_factor_title'))

@section('form')
    <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.auth.two_factor_title') }}</h1>
    <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.auth.two_factor_intro') }}</p>
    <form class="mt-8 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/two-factor-challenge') }}" data-kind="two-factor" data-redirect="{{ $intended }}" novalidate>
        <div data-code-field>
            <x-store.field name="code" :label="__('storefront.auth.two_factor_code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" dir="ltr" class="font-mono tracking-[0.4em]" />
        </div>
        <div data-recovery-field hidden>
            <x-store.field name="recovery_code" :label="__('storefront.auth.recovery_code')" autocomplete="off" dir="ltr" class="font-mono" />
        </div>
        <button type="button" class="self-start text-sm text-accent-text hover:underline" data-toggle-recovery>{{ __('storefront.auth.use_recovery') }}</button>
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
        <button type="submit" class="btn-primary btn-lg w-full">{{ __('storefront.auth.verify') }}</button>
    </form>
@endsection
