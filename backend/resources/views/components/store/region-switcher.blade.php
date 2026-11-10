{{--
    Language links (same page in the other language) and the display currency, saved with
    `POST /api/v1/storefront/currency` (cookie) and applied by reloading the page.
--}}
@props(['storefront', 'idPrefix' => 'region'])

<div class="flex flex-col gap-4">
    <div>
        <p class="field-label">{{ __('storefront.nav.language') }}</p>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($storefront['languages'] as $language)
                <a href="{{ $language['url'] }}" hreflang="{{ $language['code'] }}" lang="{{ $language['code'] }}"
                    @class(['flex h-10 items-center justify-center rounded-md border text-sm font-medium', 'border-accent-primary bg-accent-tint text-accent-text' => $language['current'], 'border-line-default hover:border-line-strong' => ! $language['current']])
                    @if ($language['current']) aria-current="true" @endif>{{ __('storefront.languages.'.$language['code']) }}</a>
            @endforeach
        </div>
    </div>
    <div data-module="currency">
        <label for="{{ $idPrefix }}-currency" class="field-label">{{ __('storefront.nav.currency') }}</label>
        <select id="{{ $idPrefix }}-currency" class="field-input" data-currency-select>
            @foreach ($storefront['currencies'] as $currency)
                <option value="{{ $currency['code'] }}" @selected($currency['code'] === $storefront['currency'])>{{ $currency['code'] }} · {{ $currency['name'] }}</option>
            @endforeach
        </select>
        <p class="field-help">{{ __('storefront.nav.currency_help') }}</p>
    </div>
</div>
