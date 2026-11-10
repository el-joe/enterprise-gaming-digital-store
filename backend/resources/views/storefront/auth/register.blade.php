@extends('layouts.auth')

@section('title', __('storefront.auth.register_title'))

@section('form')
    <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.auth.register_title') }}</h1>
    <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.auth.register_intro') }}</p>
    <form class="mt-8 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/register') }}" data-kind="register" data-redirect="{{ $intended }}" novalidate>
        <x-store.field name="name" :label="__('storefront.auth.name')" autocomplete="name" required maxlength="120" />
        <x-store.field name="email" type="email" :label="__('storefront.auth.email')" autocomplete="email" required dir="ltr" />
        <x-store.field name="password" type="password" :label="__('storefront.auth.password')" :help="__('storefront.auth.password_help')" autocomplete="new-password" required />
        <x-store.field name="password_confirmation" type="password" :label="__('storefront.auth.password_confirmation')" autocomplete="new-password" required />
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
        <button type="submit" class="btn-primary btn-lg w-full">{{ __('storefront.auth.create_account') }}</button>
        <p class="text-xs text-content-muted">{{ __('storefront.auth.register_terms') }}</p>
    </form>
    <p class="mt-6 text-center text-sm text-content-secondary">{{ __('storefront.auth.have_account') }} <a href="{{ url()->localized('storefront.login') }}" class="text-accent-text hover:underline">{{ __('storefront.auth.sign_in') }}</a></p>
@endsection
