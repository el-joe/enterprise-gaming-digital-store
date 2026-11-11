{{--
    SEO card (M15, `seo_metadata`): title, description, canonical and robots per locale. Saving
    needs `seo.manage`; without it the values are shown read-only. Needs `type`, `record`, `seoValues`.
--}}
@php($canSeo = auth()->user()?->can('seo.manage'))
<x-admin.card :title="__('admin.seo.title')" :subtitle="__('admin.seo.note')">
    <div class="flex flex-col gap-5">
        @foreach ((array) config('app.supported_locales') as $locale)
            @php($values = $seoValues[$locale] ?? [])
            <form class="flex flex-col gap-3" data-module="form" data-action="{{ route('admin.api.seo.update', ['type' => $type, 'id' => $record->getKey()]) }}" data-method="PUT" novalidate>
                <p class="text-xs font-semibold tracking-wide text-content-muted uppercase">{{ __('admin.translations.locale.'.$locale) }}</p>
                <input type="hidden" name="locale" value="{{ $locale }}">
                <x-admin.field name="title" :id="'seo-'.$locale.'-title'" :label="__('admin.seo.meta_title')" :value="$values['title'] ?? ''" maxlength="190" :dir="$locale === 'ar' ? 'rtl' : 'ltr'" data-nullable :disabled="! $canSeo" />
                <div>
                    <label for="seo-{{ $locale }}-description" class="field-label">{{ __('admin.seo.meta_description') }}</label>
                    <textarea id="seo-{{ $locale }}-description" name="description" rows="3" maxlength="320" class="admin-input py-2" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" data-nullable @disabled(! $canSeo)>{{ $values['description'] ?? '' }}</textarea>
                    <p class="field-error" data-error-for="description" hidden></p>
                </div>
                <x-admin.field name="canonical_override" :id="'seo-'.$locale.'-canonical'" :label="__('admin.seo.canonical')" :value="$values['canonical_override'] ?? ''" dir="ltr" data-nullable :help="__('admin.seo.canonical_help')" :disabled="! $canSeo" />
                <div>
                    <label for="seo-{{ $locale }}-robots" class="field-label">{{ __('admin.seo.robots') }}</label>
                    <select id="seo-{{ $locale }}-robots" name="robots" class="admin-input" data-nullable @disabled(! $canSeo)>
                        <option value="">{{ __('admin.seo.robots_default') }}</option>
                        @foreach (['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'] as $robots)
                            <option value="{{ $robots }}" @selected(($values['robots'] ?? '') === $robots)>{{ $robots }}</option>
                        @endforeach
                    </select>
                    <p class="field-error" data-error-for="robots" hidden></p>
                </div>
                <x-admin.form-alerts />
                @if ($canSeo)
                    <div><button type="submit" class="admin-btn">{{ __('admin.seo.save') }}</button></div>
                @endif
            </form>
        @endforeach
    </div>
</x-admin.card>
