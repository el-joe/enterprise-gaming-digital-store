@extends('layouts.auth')

@section('title', __('storefront.auth.reset_title'))

@section('form')
    <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.auth.reset_title') }}</h1>
    <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.auth.reset_intro') }}</p>
    <form class="mt-8 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/reset-password') }}" data-kind="reset" data-redirect="{{ url()->localized('storefront.login') }}" novalidate>
        <input type="hidden" name="token" value="{{ $token }}">
        <x-store.field name="email" type="email" :label="__('storefront.auth.email')" :value="$email" autocomplete="email" required dir="ltr" />
        <x-store.field name="password" type="password" :label="__('storefront.auth.new_password')" :help="__('storefront.auth.password_help')" autocomplete="new-password" required />
        <x-store.field name="password_confirmation" type="password" :label="__('storefront.auth.password_confirmation')" autocomplete="new-password" required />
        <p class="field-error" data-error-for="token" hidden></p>
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
        <button type="submit" class="btn-primary btn-lg w-full">{{ __('storefront.auth.reset_submit') }}</button>
    </form>
@endsection
