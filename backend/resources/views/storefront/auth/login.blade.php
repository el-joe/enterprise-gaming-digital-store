@extends('layouts.auth')

@section('title', __('storefront.auth.login_title'))

@section('form')
    <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.auth.login_title') }}</h1>
    <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.auth.login_intro') }}</p>
    <form class="mt-8 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/login') }}" data-kind="login"
        data-redirect="{{ $intended }}" data-two-factor-url="{{ url()->localized('storefront.two-factor') }}" novalidate>
        <x-store.field name="email" type="email" :label="__('storefront.auth.email')" autocomplete="email" required dir="ltr" />
        <x-store.field name="password" type="password" :label="__('storefront.auth.password')" autocomplete="current-password" required />
        <div class="flex items-center justify-between gap-3 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="remember" value="1" class="size-4 accent-(--color-accent-primary)"> {{ __('storefront.auth.remember') }}</label>
            <a href="{{ url()->localized('storefront.forgot-password') }}" class="text-accent-text hover:underline">{{ __('storefront.auth.forgot') }}</a>
        </div>
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
        <button type="submit" class="btn-primary btn-lg w-full">{{ __('storefront.auth.sign_in') }}</button>
    </form>
    <p class="mt-6 text-center text-sm text-content-secondary">{{ __('storefront.auth.new_here') }} <a href="{{ url()->localized('storefront.register') }}" class="text-accent-text hover:underline">{{ __('storefront.auth.create_account') }}</a></p>
@endsection
