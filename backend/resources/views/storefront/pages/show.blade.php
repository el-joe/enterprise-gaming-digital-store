@extends('layouts.storefront')

@section('title', $title)

@section('content')
    <div class="container-page max-w-3xl pt-6 md:pt-10">
        <x-store.breadcrumbs :items="[__('storefront.nav.home_short') => url()->localized('storefront.home'), $title => null]" />
        <h1 class="mt-4 font-display text-[32px] leading-[38px] font-bold md:text-[40px] md:leading-[46px]">{{ $title }}</h1>
        @if ($updatedAt)
            <p class="mt-2 text-xs text-content-muted">{{ __('storefront.pages.updated', ['date' => $updatedAt->translatedFormat('j F Y')]) }}</p>
        @endif

        <article class="prose-store mt-8">
            @if ($body !== null)
                {!! $body !!}
            @else
                @foreach ((array) __("storefront.pages.{$slug}.body") as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
                <p class="text-xs text-content-muted">{{ __('storefront.pages.built_in_note') }}</p>
            @endif
        </article>
    </div>
@endsection
