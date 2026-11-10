@extends('layouts.auth')

@section('title', __('storefront.auth.forgot_title'))

@section('form')
    <h1 class="font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.auth.forgot_title') }}</h1>
    <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.auth.forgot_intro') }}</p>
    <form class="mt-8 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/forgot-password') }}" data-kind="message" novalidate>
        <x-store.field name="email" type="email" :label="__('storefront.auth.email')" autocomplete="email" required dir="ltr" />
        <div class="alert-success" data-form-success hidden role="status"><x-store.icon name="check" class="mt-0.5" /><p data-form-success-text></p></div>
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
        <button type="submit" class="btn-primary btn-lg w-full">{{ __('storefront.auth.send_link') }}</button>
    </form>
    <p class="mt-6 text-center text-sm"><a href="{{ url()->localized('storefront.login') }}" class="text-accent-text hover:underline">{{ __('storefront.auth.back_to_login') }}</a></p>
@endsection
