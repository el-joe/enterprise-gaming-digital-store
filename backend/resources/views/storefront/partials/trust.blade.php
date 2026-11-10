<section class="container-page pt-14 md:pt-[72px]" aria-label="{{ __('storefront.trust.label') }}">
    <ul class="grid gap-6 rounded-xl border border-line-subtle bg-surface-raised p-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach (['secure', 'fast', 'region', 'support'] as $point)
            <li class="flex gap-3">
                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-md bg-accent-tint text-accent-text"><x-store.icon name="shield" :size="18" /></span>
                <div>
                    <p class="text-sm font-semibold">{{ __("storefront.trust.{$point}.title") }}</p>
                    <p class="text-xs leading-5 text-content-secondary">{{ __("storefront.trust.{$point}.text") }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</section>
