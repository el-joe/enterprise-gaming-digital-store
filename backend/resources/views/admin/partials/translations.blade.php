{{--
    Translations card (M15): the record's text in each non-English locale, saved to
    `content_translations` through the admin API. Empty fields fall back to the English text.
    Needs `type`, `fields` (name => text|textarea|html), `record`, `translationValues`, `canManage`.
--}}
@foreach (array_diff((array) config('app.supported_locales'), [config('app.fallback_locale')]) as $locale)
    <x-admin.card :title="__('admin.translations.title', ['language' => __('admin.translations.locale.'.$locale)])" :subtitle="__('admin.translations.note')">
        <form class="flex flex-col gap-3" data-module="form" data-action="{{ route('admin.api.translations.'.$type.'.update', ['id' => $record->getKey()]) }}" data-method="PUT" novalidate>
            <input type="hidden" name="locale" value="{{ $locale }}">
            @foreach ($fields as $field => $kind)
                @php($inputId = 'tr-'.$locale.'-'.$field)
                <div>
                    <label for="{{ $inputId }}" class="field-label">{{ \App\DataTables\Cell::label('translatable_field', $field) }}</label>
                    @if ($kind === 'text')
                        <input id="{{ $inputId }}" name="values[{{ $field }}]" value="{{ $translationValues[$locale][$field] ?? '' }}" maxlength="500" class="admin-input" dir="{{ $locale === 'ar' ? 'rtl' : 'auto' }}" lang="{{ $locale }}" placeholder="{{ \Illuminate\Support\Str::limit((string) $record->getAttribute($field), 80) }}" @disabled(! $canManage)>
                    @else
                        <textarea id="{{ $inputId }}" name="values[{{ $field }}]" rows="{{ $kind === 'html' ? 6 : 3 }}" class="admin-input py-2" dir="{{ $locale === 'ar' ? 'rtl' : 'auto' }}" lang="{{ $locale }}" @disabled(! $canManage)>{{ $translationValues[$locale][$field] ?? '' }}</textarea>
                    @endif
                    <p class="field-error" data-error-for="values.{{ $field }}" hidden></p>
                </div>
            @endforeach
            <x-admin.form-alerts />
            @if ($canManage)
                <div><button type="submit" class="admin-btn-primary">{{ __('admin.translations.save') }}</button></div>
            @endif
        </form>
    </x-admin.card>
@endforeach
