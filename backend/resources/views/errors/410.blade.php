@extends('layouts.storefront')

{{-- 410 Gone (M18): a removed product or an admin 410 redirect. Without a matched route the storefront locale middleware never ran: take the language from the path. --}}
@if (request()->route() === null)
    @php(app()->setLocale(\App\Http\Support\LocalizedRoutes::localeOfPath(request()->path())))
@endif

@section('title', __('storefront.errors.410_title'))
@section('robots', 'noindex,follow')

@section('content')
    <section class="relative isolate overflow-hidden">
        <x-store.dial play class="absolute top-1/2 left-1/2 -z-10 h-[140%] -translate-x-1/2 -translate-y-1/2 text-ember-500 opacity-25" />
        <div class="container-page flex min-h-[520px] flex-col items-center justify-center gap-4 py-16 text-center">
            <span class="block w-48 text-content-muted">{!! file_get_contents(resource_path('assets/images/empty-states/not-found-404-240x200.svg')) !!}</span>
            <p class="font-display text-[56px] leading-[60px] font-extrabold text-ember-400">410</p>
            <h1 class="font-display text-[28px] leading-[34px] font-bold">{{ __('storefront.errors.410_title') }}</h1>
            <p class="max-w-md text-sm text-content-secondary">{{ __('storefront.errors.410_text') }}</p>
            <div class="mt-2 flex flex-wrap justify-center gap-3">
                <a href="{{ url()->localized('storefront.home') }}" class="btn-primary">{{ __('storefront.common.back_to_store') }}</a>
                <a href="{{ url()->localized('storefront.search') }}" class="btn-secondary"><x-store.icon name="search" :size="18" /> {{ __('storefront.errors.search') }}</a>
            </div>
            @include('errors.partials.popular')
        </div>
    </section>
@endsection
