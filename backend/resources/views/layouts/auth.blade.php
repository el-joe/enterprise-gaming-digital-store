{{-- Auth split screen (Figma `Auth Sign In`): art and promise on the inline start, the form on the inline end. --}}
@extends('layouts.storefront')

@section('header', 'none')
@section('robots', 'noindex,follow')

@section('content')
    <div class="grid min-h-screen lg:grid-cols-[1fr_1fr]">
        <div class="relative isolate hidden overflow-hidden bg-surface-base lg:flex lg:flex-col lg:justify-between lg:p-12">
            <x-store.dial play class="absolute -top-1/4 end-[-10%] -z-10 h-[90%] text-ultra-400 opacity-70" />
            <div class="absolute inset-0 -z-10 bg-linear-to-t from-surface-base via-surface-base/60 to-transparent" aria-hidden="true"></div>
            <a href="{{ url()->localized('storefront.home') }}" class="self-start"><x-brand.logo :height="36" eager /></a>
            <div>
                <p class="font-display text-[40px] leading-[46px] font-bold">{{ __('storefront.auth.promise_title') }}</p>
                <p class="mt-3 text-content-secondary">{{ __('storefront.auth.promise_text') }}</p>
            </div>
        </div>
        <div class="flex flex-col justify-center px-4 py-10 md:px-8">
            <a href="{{ url()->localized('storefront.home') }}" class="mb-10 self-start lg:hidden"><x-brand.logo :height="32" eager /></a>
            <div class="mx-auto w-full max-w-[440px]">
                @yield('form')
            </div>
        </div>
    </div>
@endsection
