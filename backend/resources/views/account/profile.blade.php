@extends('layouts.account')

@section('title', __('account.profile.title'))
@section('nav', 'profile')

@section('page')
    <x-account.page-head :title="__('account.profile.title')" :subtitle="__('account.profile.subtitle')" />

    <form class="flex max-w-2xl flex-col gap-4 rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-6" novalidate
        data-module="account-form" data-action="{{ url('/api/v1/account/profile') }}" data-method="PATCH" data-success="reload">
        <x-store.field name="name" id="profile-name" :label="__('account.fields.name')" :value="$profile['name']" maxlength="120" autocomplete="name" required />
        <div>
            <span class="field-label">{{ __('account.fields.email') }}</span>
            <p class="flex min-h-11 items-center gap-2 rounded-md border border-line-subtle bg-surface-base px-3.5 text-sm text-content-secondary"><span dir="ltr">{{ $profile['email'] }}</span>
                <x-account.badge :tone="$profile['email_verified'] ? 'success' : 'warning'">{{ $profile['email_verified'] ? __('account.profile.verified') : __('account.profile.unverified') }}</x-account.badge>
            </p>
            <p class="field-help">{{ __('account.profile.email_help') }}</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="profile-country" class="field-label">{{ __('account.fields.country') }}</label>
                <select id="profile-country" name="country_code" class="field-input" data-nullable aria-describedby="profile-country-error">
                    <option value="">{{ __('account.profile.not_set') }}</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country['code'] }}" @selected($profile['country_code'] === $country['code'])>{{ $country['name'] }}</option>
                    @endforeach
                </select>
                <p id="profile-country-error" class="field-error" data-error-for="country_code" hidden></p>
            </div>
            <div>
                <label for="profile-locale" class="field-label">{{ __('account.fields.language') }}</label>
                <select id="profile-locale" name="locale" class="field-input" data-nullable aria-describedby="profile-locale-error">
                    <option value="">{{ __('account.profile.not_set') }}</option>
                    @foreach ($locales as $locale)
                        <option value="{{ $locale }}" @selected($profile['locale'] === $locale)>{{ __('storefront.languages.'.$locale) }}</option>
                    @endforeach
                </select>
                <p id="profile-locale-error" class="field-error" data-error-for="locale" hidden></p>
            </div>
        </div>
        <div>
            <label for="profile-currency" class="field-label">{{ __('account.fields.display_currency') }}</label>
            <select id="profile-currency" name="display_currency" class="field-input" data-nullable aria-describedby="profile-currency-help profile-currency-error">
                <option value="">{{ __('account.profile.currency_auto') }}</option>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency['code'] }}" @selected($profile['display_currency'] === $currency['code'])>{{ $currency['code'] }} · {{ $currency['name'] }}</option>
                @endforeach
            </select>
            <p id="profile-currency-help" class="field-help">{{ __('account.profile.currency_help') }}</p>
            <p id="profile-currency-error" class="field-error" data-error-for="display_currency" hidden></p>
        </div>
        <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
        <div><button type="submit" class="btn-primary">{{ __('account.common.save') }}</button></div>
    </form>
@endsection
