{{-- Promotional banners (admin `home_promo` or a section's banner items); textless art fallback. --}}
@php
    $fallbackArt = ['promo/01-flash-1200x600', 'promo/02-bundle-1200x600', 'promo/03-new-drop-1200x600'];
    $bare = $bare ?? false;
@endphp

@if ($banners !== [])
    <div @class(['container-page pt-14 md:pt-[72px]' => ! $bare])>
        <ul class="grid gap-4 md:grid-cols-2 lg:gap-6">
            @foreach ($banners as $banner)
                <li class="keycut relative isolate min-h-44 overflow-hidden border border-line-subtle bg-surface-raised md:min-h-56">
                    @if ($banner['image']['desktop'])
                        <img src="{{ $banner['image']['desktop']['formats']['webp'] ?? $banner['image']['desktop']['url'] }}" alt="" class="absolute inset-0 -z-10 size-full object-cover" loading="lazy" decoding="async">
                    @else
                        <x-store.art :path="$fallbackArt[$loop->index % 3]" width="1200" height="600" rtl class="absolute inset-0 -z-10 size-full object-cover" />
                    @endif
                    <div class="absolute inset-0 -z-10 bg-linear-to-r from-ink-950/90 via-ink-950/50 to-transparent rtl:bg-linear-to-l" aria-hidden="true"></div>
                    <div class="flex h-full flex-col justify-end gap-1 p-5 md:p-6">
                        <h3 class="font-display text-2xl font-bold">{{ $banner['title'] }}</h3>
                        @if ($banner['subtitle'])<p class="text-sm text-content-secondary">{{ $banner['subtitle'] }}</p>@endif
                        @if ($banner['link'])
                            <a href="{{ $banner['link']['url'] }}" class="mt-2 inline-flex items-center gap-1.5 self-start text-sm font-semibold text-accent-text after:absolute after:inset-0 hover:underline">
                                {{ $banner['cta_label'] ?: __('storefront.common.shop_now') }} <x-store.icon name="arrow" :size="16" />
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
