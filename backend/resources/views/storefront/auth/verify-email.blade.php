@extends('layouts.auth')

@section('title', __('storefront.auth.verify_title'))

@section('form')
    <span class="inline-flex size-14 items-center justify-center rounded-se-[28px] rounded-ss-md rounded-es-md rounded-ee-md bg-accent-tint text-accent-text" aria-hidden="true"><x-store.icon name="mail" :size="26" /></span>
    <h1 class="mt-6 font-display text-[32px] leading-[38px] font-bold">{{ __('storefront.auth.verify_title') }}</h1>
    <p class="mt-2 text-sm text-content-secondary">{{ __('storefront.auth.verify_intro') }}</p>
    <form class="mt-8 flex flex-col gap-4" data-module="auth-form" data-action="{{ url('/email/verification-notification') }}" data-kind="message" novalidate>
        <div class="alert-success" data-form-success hidden role="status"><x-store.icon name="check" class="mt-0.5" /><p data-form-success-text></p></div>
        <div class="alert-danger" data-form-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5" /><p data-form-error-text></p></div>
        <button type="submit" class="btn-primary btn-lg w-full">{{ __('storefront.auth.resend') }}</button>
    </form>
    <p class="mt-6 text-center text-sm"><a href="{{ url()->localized('storefront.home') }}" class="text-accent-text hover:underline">{{ __('storefront.common.back_to_store') }}</a></p>
@endsection
