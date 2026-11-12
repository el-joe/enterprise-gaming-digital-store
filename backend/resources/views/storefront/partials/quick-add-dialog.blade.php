{{--
    Variant chooser of the product cards' bag button (Storefront v2, owner request): one modal
    <dialog> per page (native focus containment, Esc closes), filled by resources/js/modules/
    quick-add.js from `/api/v1/storefront/catalog/products/{slug}/quick-add`: face value, region,
    selling price in the display currency and availability per variant (unavailable ones shown
    but disabled), quantity and the product's delivery fields. Server field errors show inline.
--}}
<dialog class="m-auto w-[min(94vw,520px)] rounded-xl rounded-se-[28px] border border-line-default bg-surface-raised p-0 text-content-primary shadow-overlay backdrop:bg-black/75" aria-labelledby="quick-add-title" data-quick-add-dialog
    data-label-unavailable="{{ __('storefront.catalog.unavailable') }}"
    data-label-in-stock="{{ __('storefront.availability.in_stock') }}"
    data-label-limited="{{ __('storefront.availability.limited') }}"
    data-label-out-of-stock="{{ __('storefront.availability.out_of_stock') }}"
    data-label-required="{{ __('storefront.catalog.fields_note') }}">
    <form class="flex max-h-[88vh] flex-col" novalidate data-quick-add-form>
        <div class="flex items-start gap-3 border-b border-line-subtle p-4">
            <div class="keycut-sm relative size-14 shrink-0 overflow-hidden bg-surface-overlay" data-qa-image></div>
            <div class="min-w-0 flex-1">
                <p class="eyebrow text-content-muted">{{ __('storefront.catalog.quick_add_title') }}</p>
                <h2 id="quick-add-title" class="truncate text-lg leading-7 font-semibold" data-qa-name></h2>
            </div>
            <button type="button" class="-me-2 inline-flex size-11 items-center justify-center rounded-md text-content-secondary hover:text-content-primary" data-qa-close>
                <x-store.icon name="x" :label="__('storefront.catalog.cancel')" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-sm text-content-secondary" role="status" data-qa-loading>{{ __('storefront.catalog.loading_options') }}</p>

            <fieldset class="flex flex-col gap-2" data-qa-variants-wrap hidden>
                <legend class="mb-2 text-sm font-semibold">{{ __('storefront.catalog.choose_option') }}</legend>
                <div class="flex flex-col gap-2" data-qa-variants></div>
                <p class="field-error" data-error-for="variant_id" hidden></p>
            </fieldset>

            <div class="mt-4 flex flex-col gap-3" data-qa-fields hidden></div>

            <div class="mt-4" data-qa-quantity-wrap hidden>
                <label for="qa-quantity" class="field-label">{{ __('storefront.catalog.quantity') }}</label>
                <input id="qa-quantity" name="quantity" type="number" min="1" max="10" value="1" inputmode="numeric" class="field-input w-28" dir="ltr">
                <p class="field-error" data-error-for="quantity" hidden></p>
            </div>

            <div class="alert-danger mt-4" role="alert" data-form-error hidden>
                <x-store.icon name="alert" class="mt-0.5" />
                <p data-form-error-text></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-subtle p-4">
            <a href="#" class="text-sm text-accent-text hover:underline" data-qa-product-link>{{ __('storefront.catalog.open_product') }}</a>
            <div class="flex gap-2">
                <button type="button" class="btn-secondary" data-qa-close>{{ __('storefront.catalog.cancel') }}</button>
                <button type="submit" class="btn-primary" data-qa-submit disabled><x-store.icon name="bag" :size="16" /> {{ __('storefront.catalog.add') }}</button>
            </div>
        </div>
    </form>
</dialog>
