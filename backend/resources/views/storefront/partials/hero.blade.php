{{--
    Home hero carousel (owner decision 8): admin `hero` banners, or the three delivered hero
    concepts when none is live. Auto-advance every few seconds (resources/js/modules/hero.js)
    with a visible pause/play button and page indicators; no auto-advance with reduced motion,
    and none while the shopper hovers, focuses, touches or swipes. Mobile crops have no calm
    area, so the copy sits below the art with a scrim (art-on-top layout of the Figma frame).
--}}
@php
    $defaults = [
        ['art' => 'hero-01-keycut', 'key' => 'one', 'href' => url()->localized('storefront.category', ['slug' => 'gift-cards'])],
        ['art' => 'hero-02-vault-dial', 'key' => 'two', 'href' => url()->localized('storefront.category', ['slug' => 'game-top-ups'])],
        ['art' => 'hero-03-glass-cards', 'key' => 'three', 'href' => url()->localized('storefront.platforms.index')],
    ];

    $slides = $banners !== []
        ? array_map(static fn (array $banner): array => [
            'title' => $banner['title'],
            'subtitle' => $banner['subtitle'],
            'cta' => $banner['cta_label'],
            'href' => $banner['link']['url'] ?? null,
            'desktop' => $banner['image']['desktop'],
            'mobile' => $banner['image']['mobile'] ?? $banner['image']['desktop'],
            'art' => null,
        ], $banners)
        : array_map(static fn (array $slide): array => [
            'title' => __("storefront.hero.{$slide['key']}.title"),
            'subtitle' => __("storefront.hero.{$slide['key']}.subtitle"),
            'cta' => __("storefront.hero.{$slide['key']}.cta"),
            'href' => $slide['href'],
            'desktop' => null,
            'mobile' => null,
            'art' => $slide['art'],
        ], $defaults);
@endphp

<section class="relative overflow-hidden" aria-roledescription="{{ __('storefront.hero.carousel') }}" aria-label="{{ __('storefront.hero.label') }}"
    data-module="hero" data-interval="{{ (int) config('storefront.hero_interval_seconds') * 1000 }}">
    <div class="relative" data-hero-track>
        @foreach ($slides as $index => $slide)
            <div class="relative grid min-h-[560px] md:min-h-[520px] lg:min-h-[620px]" role="group" aria-roledescription="{{ __('storefront.hero.slide') }}"
                aria-label="{{ __('storefront.hero.slide_of', ['index' => $index + 1, 'count' => count($slides)]) }}" data-hero-slide @if (! $loop->first) hidden @endif>
                {{-- Art and scrim live in a frame capped at 1920px and centred, so on wide screens the scrim
                     lines up with the content column instead of the viewport edge; beyond 1920px the frame's
                     sides fade into the canvas (.hero-frame). --}}
                <div class="hero-frame absolute inset-0 mx-auto w-full max-w-[1920px]">
                    <picture class="absolute inset-0 -z-0">
                        @if ($slide['art'])
                            <source media="(max-width: 767px)" srcset="{{ Vite::asset("resources/assets/images/hero/{$slide['art']}-mobile-1080x1350.webp") }}">
                            <img src="{{ Vite::asset("resources/assets/images/hero/{$slide['art']}-desktop-2400x1000.webp") }}" alt="" width="2400" height="1000"
                                class="h-[62%] w-full object-cover object-top md:h-full md:object-[75%_center] rtl:md:-scale-x-100" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                        @else
                            @if ($slide['mobile'])<source media="(max-width: 767px)" srcset="{{ $slide['mobile']['formats']['webp'] ?? $slide['mobile']['url'] }}">@endif
                            <img src="{{ $slide['desktop']['formats']['webp'] ?? $slide['desktop']['url'] ?? '' }}" alt="{{ $slide['desktop']['alt'] ?? '' }}" class="h-[62%] w-full object-cover md:h-full" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                        @endif
                    </picture>
                    <div class="hero-scrim absolute inset-0" aria-hidden="true"></div>
                </div>
                <div class="container-page relative flex items-end pb-24 md:items-center md:pb-0">
                    <div class="flex max-w-xl flex-col items-start gap-4 md:gap-5">
                        <span class="rounded-xs bg-accent-tint px-2 py-0.5 text-xs font-medium text-accent-text">{{ __('storefront.hero.badge') }}</span>
                        <h1 @class(['font-display text-[44px] leading-[48px] font-extrabold md:text-[56px] md:leading-[60px] lg:text-[72px] lg:leading-[76px]']) @if (! $loop->first) aria-hidden="true" @endif>{{ $slide['title'] }}</h1>
                        @if ($slide['subtitle'])
                            <p class="max-w-lg text-base leading-[26px] text-content-secondary">{{ $slide['subtitle'] }}</p>
                        @endif
                        <div class="flex w-full flex-wrap gap-3 sm:w-auto">
                            @if ($slide['href'] && $slide['cta'])
                                <a href="{{ $slide['href'] }}" class="btn-primary btn-lg w-full sm:w-auto">{{ $slide['cta'] }} <x-store.icon name="arrow" :size="18" /></a>
                            @endif
                            @if ($loop->first && $banners === [])
                                <a href="{{ url()->localized('storefront.category', ['slug' => 'game-top-ups']) }}" class="btn-secondary btn-lg hidden sm:inline-flex">{{ __('storefront.hero.secondary') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if (count($slides) > 1)
        <div class="container-page absolute inset-x-0 bottom-4 flex items-center gap-3 md:bottom-8">
            <button type="button" class="inline-flex size-9 items-center justify-center rounded-full border border-line-default bg-surface-base/80 text-content-primary hover:border-line-strong"
                data-hero-toggle aria-label="{{ __('storefront.hero.pause') }}" data-label-pause="{{ __('storefront.hero.pause') }}" data-label-play="{{ __('storefront.hero.play') }}">
                <x-store.icon name="pause" :size="16" data-icon-pause />
                <x-store.icon name="play" :size="16" data-icon-play class="hidden" />
            </button>
            <div class="flex items-center gap-2" role="group" aria-label="{{ __('storefront.hero.pages') }}">
                @foreach ($slides as $index => $slide)
                    <button type="button" class="h-2 w-6 rounded-full bg-ink-600 transition-all aria-[current=true]:w-10 aria-[current=true]:bg-accent-primary"
                        data-hero-dot="{{ $index }}" aria-label="{{ __('storefront.hero.go_to', ['index' => $index + 1]) }}" @if ($loop->first) aria-current="true" @endif></button>
                @endforeach
            </div>
        </div>
    @endif
</section>
