{{--
    Newsletter band. `POST /api/v1/storefront/newsletter` is planned (API_DOCUMENTATION.md 2),
    not built, so the band explains that sign-up opens soon instead of showing a form that
    cannot work.
--}}
<section class="container-page pt-14 md:pt-[72px]" aria-labelledby="newsletter-title">
    <div class="keycut flex flex-col gap-4 border border-accent-primary/40 bg-field-volt p-6 md:flex-row md:items-center md:justify-between md:p-8">
        <div>
            <h2 id="newsletter-title" class="font-display text-2xl leading-8 font-bold md:text-[28px]">{{ $title ?: __('storefront.newsletter.title') }}</h2>
            <p class="mt-1 text-sm text-content-secondary">{{ $subtitle ?: __('storefront.newsletter.subtitle') }}</p>
        </div>
        <p class="inline-flex items-center gap-2 rounded-md border border-line-default bg-surface-sunken px-4 py-3 text-sm text-content-secondary">
            <x-store.icon name="mail" :size="18" class="text-accent-text" /> {{ __('storefront.newsletter.soon') }}
        </p>
    </div>
</section>
