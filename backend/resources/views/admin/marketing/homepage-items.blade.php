{{-- Homepage section items (M15): the manual items of the section, replaced as one list. --}}
<x-admin.card :title="__('admin.homepage.items')" :subtitle="__('admin.homepage.items_note')">
    <form class="flex flex-col gap-3" data-module="form" data-action="{{ url('/admin/api/homepage-sections/'.$record->getKey().'/items') }}" data-method="PUT" data-success="reload" novalidate>
        <div>
            <label for="hs-items" class="field-label">{{ __('admin.homepage.items_json') }}</label>
            <textarea id="hs-items" name="items" rows="8" class="admin-input py-2 font-mono text-xs" dir="ltr" data-json @disabled(! $canManage)>{{ json_encode($panel['items'], JSON_PRETTY_PRINT) }}</textarea>
            <p class="field-help">{{ __('admin.homepage.items_help') }}</p>
            <p class="field-error" data-error-for="items" hidden></p>
        </div>
        <x-admin.form-alerts />
        @if ($canManage)
            <div><button type="submit" class="admin-btn-primary">{{ __('admin.common.save') }}</button></div>
        @endif
    </form>
</x-admin.card>
