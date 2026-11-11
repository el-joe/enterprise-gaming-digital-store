{{--
    Analytics consent (M18, SECURITY.md section 9: cookie consent gates GTM and GA). Rendered only
    while a tag is configured. Hidden until `consent.js` finds no saved choice; the tag loads only
    after "Accept". Declining keeps every analytics request off. The choice is a first-party
    cookie (`bc_consent`, 180 days) and can be changed from the footer.
--}}
<section class="fixed inset-x-0 bottom-0 z-40 p-4 md:bottom-4 md:start-4 md:end-auto md:max-w-md md:p-0" data-module="consent" data-consent-banner hidden aria-labelledby="consent-title" role="region">
    <div class="keycut border border-line-default bg-surface-raised p-5 shadow-lg">
        <h2 id="consent-title" class="text-base font-semibold">{{ __('storefront.consent.title') }}</h2>
        <p class="mt-2 text-sm leading-6 text-content-secondary">
            {{ __('storefront.consent.text') }}
            <a href="{{ url()->localized('storefront.pages.show', ['slug' => 'privacy']) }}" class="text-accent-text underline">{{ __('storefront.consent.privacy') }}</a>
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" class="btn-primary" data-consent-choice="granted">{{ __('storefront.consent.accept') }}</button>
            <button type="button" class="btn-secondary" data-consent-choice="denied">{{ __('storefront.consent.decline') }}</button>
        </div>
    </div>
</section>
