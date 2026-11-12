{{--
    Admin → Marketing → Homepage sections (Storefront v2, Figma 116:11200 and the drawer 116:11898):
    the managed sections in home order with drag handles (pointer events) and keyboard up/down
    buttons, type, English and Arabic title, rule, the computed "See all" and visibility; a drawer
    to add or edit a section (type, titles, rule, period, item limit 4–24, read-only "See all",
    visibility); and the category rails, read-only, after the managed sections. Behaviour:
    resources/js/admin/homepage-sections.js. Writes go to /admin/api/homepage-sections (CSRF,
    permission `content.homepage`, audited).
--}}
@extends('layouts.admin')

@php
    $types = ['hero_banners', 'category_tiles', 'brand_logos', 'product_rail'];
    $rules = ['best_sellers', 'newest', 'on_sale', 'category', 'brand', 'manual'];
    $badge = [
        'hero_banners' => 'bg-ultra-500/15 text-ultra-400',
        'category_tiles' => 'bg-aqua-500/15 text-aqua-500',
        'brand_logos' => 'bg-sun-500/15 text-sun-500',
        'product_rail' => 'bg-volt-500/15 text-accent-text',
    ];
@endphp

@section('title', __('admin.storefront.title'))
@section('heading', __('admin.nav.marketing'))

@section('content')
    @include('admin.partials.tabs', ['current' => $screen::slug()])

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold">{{ __('admin.storefront.title') }}</h1>
            <p class="mt-1 text-sm text-content-secondary">{{ __('admin.storefront.intro') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $previewUrl }}" class="admin-btn min-h-11" target="_blank" rel="noopener"><x-store.icon name="eye" :size="16" /> {{ __('admin.storefront.preview') }}</a>
            @if ($canManage)
                <button type="button" class="admin-btn-primary min-h-11" data-section-new><x-store.icon name="plus" :size="16" /> {{ __('admin.storefront.add') }}</button>
            @endif
        </div>
    </div>

    <div data-module="homepage-sections" data-api="{{ $apiUrl }}" data-can-manage="{{ $canManage ? '1' : '0' }}" data-saved="{{ __('admin.storefront.reordered') }}">
        <div class="admin-card overflow-x-auto">
            <div class="grid min-w-[880px] grid-cols-[56px_40px_150px_minmax(0,1fr)_200px_220px_90px_136px] items-center gap-2 border-b border-line-subtle px-3 py-2 text-xs font-medium text-content-muted" aria-hidden="true">
                <span></span>
                <span>{{ __('admin.storefront.col_position') }}</span>
                <span>{{ __('admin.storefront.col_type') }}</span>
                <span>{{ __('admin.storefront.col_title') }}</span>
                <span>{{ __('admin.storefront.col_rule') }}</span>
                <span>{{ __('admin.storefront.col_see_all') }}</span>
                <span>{{ __('admin.storefront.col_visible') }}</span>
                <span>{{ __('admin.storefront.col_actions') }}</span>
            </div>
            <ol class="min-w-[880px]" data-section-list aria-label="{{ __('admin.storefront.title') }}">
                @foreach ($sections as $section)
                    @php $name = $section['title'] ?: $section['default_title']; @endphp
                    <li class="grid grid-cols-[56px_40px_150px_minmax(0,1fr)_200px_220px_90px_136px] items-center gap-2 border-b border-line-subtle px-3 py-2 last:border-b-0 data-[dragging]:bg-accent-tint data-[dragging]:outline data-[dragging]:outline-accent-primary"
                        data-section-row data-id="{{ $section['id'] }}" data-section="{{ json_encode($section) }}">
                        <span>
                            @if ($canManage)
                                <button type="button" class="inline-flex size-11 cursor-grab touch-none items-center justify-center rounded-md text-content-muted hover:bg-surface-sunken active:cursor-grabbing" data-drag-handle aria-label="{{ __('admin.storefront.drag', ['name' => $name]) }}">
                                    <x-store.icon name="grid" :size="16" />
                                </button>
                            @endif
                        </span>
                        <span class="font-mono text-sm text-content-muted" data-position>{{ $loop->iteration }}</span>
                        <span><span class="inline-flex rounded-xs px-2 py-0.5 text-xs font-medium {{ $badge[$section['type']] ?? 'bg-surface-sunken text-content-secondary' }}">{{ __('admin.storefront.types.'.$section['type']) }}</span></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium">{{ $name }}</span>
                            <span class="block truncate text-xs text-content-muted" dir="rtl" lang="ar">{{ $section['title_ar'] ?: '—' }}</span>
                        </span>
                        <span class="truncate text-sm text-content-secondary">{{ $section['rule_label'] }}</span>
                        <span class="truncate font-mono text-xs text-content-secondary" dir="ltr">{{ $section['see_all'] ?? __('admin.storefront.none') }}</span>
                        <span><span @class(['inline-flex rounded-xs px-2 py-0.5 text-xs font-medium', 'bg-status-success-tint text-status-success' => $section['is_active'], 'bg-surface-sunken text-content-muted' => ! $section['is_active']])>{{ $section['is_active'] ? __('admin.storefront.shown') : __('admin.storefront.hidden') }}</span></span>
                        <span class="flex items-center gap-1">
                            @if ($canManage)
                                <button type="button" class="inline-flex size-11 items-center justify-center rounded-md hover:bg-surface-sunken disabled:opacity-30" data-move="up" aria-label="{{ __('admin.storefront.move_up', ['name' => $name]) }}"><x-store.icon name="chevron-down" :size="16" class="rotate-180" /></button>
                                <button type="button" class="inline-flex size-11 items-center justify-center rounded-md hover:bg-surface-sunken disabled:opacity-30" data-move="down" aria-label="{{ __('admin.storefront.move_down', ['name' => $name]) }}"><x-store.icon name="chevron-down" :size="16" /></button>
                            @endif
                            @if ($section['editable'])
                                <button type="button" class="inline-flex size-11 items-center justify-center rounded-md hover:bg-surface-sunken" data-section-edit aria-label="{{ __('admin.storefront.edit') }}: {{ $name }}"><x-store.icon name="pencil" :size="16" /></button>
                            @else
                                <a href="{{ $section['edit_url'] }}" class="inline-flex size-11 items-center justify-center rounded-md hover:bg-surface-sunken" aria-label="{{ __('admin.storefront.open_page') }}: {{ $name }}"><x-store.icon name="external" :size="16" /></a>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>

        <section class="admin-card mt-4 overflow-x-auto" aria-labelledby="rails-title">
            <div class="flex flex-wrap items-baseline gap-2 border-b border-line-subtle px-4 py-3">
                <x-store.icon name="lock" :size="16" class="text-content-muted" />
                <h2 id="rails-title" class="text-base font-semibold">{{ __('admin.storefront.from_categories') }}</h2>
                <p class="text-xs text-content-muted">{{ __('admin.storefront.from_categories_note') }}</p>
            </div>
            <ol class="min-w-[720px]">
                @forelse ($rails as $rail)
                    <li class="grid grid-cols-[56px_40px_150px_minmax(0,1fr)_220px_160px] items-center gap-2 border-b border-line-subtle px-3 py-2 text-content-secondary last:border-b-0" data-category-rail>
                        <span class="inline-flex size-11 items-center justify-center text-content-muted"><x-store.icon name="lock" :size="14" /></span>
                        <span class="font-mono text-sm text-content-muted">{{ count($sections) + $loop->iteration }}</span>
                        <span><span class="inline-flex rounded-xs bg-aqua-500/15 px-2 py-0.5 text-xs font-medium text-aqua-500">{{ __('admin.storefront.types.category_rail') }}</span></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-content-primary">{{ __('admin.storefront.from_categories') }}: {{ $rail['name'] }}</span>
                            <span class="block truncate text-xs text-content-muted">{{ __('admin.storefront.rail_meta', ['position' => $rail['position']]) }} · <span dir="rtl" lang="ar">{{ $rail['name_ar'] ?: '—' }}</span></span>
                        </span>
                        <span class="truncate font-mono text-xs" dir="ltr">{{ $rail['see_all'] }}</span>
                        <a href="{{ $rail['edit_url'] }}" class="inline-flex min-h-11 items-center gap-1 text-sm text-accent-text hover:underline">{{ __('admin.storefront.edit_on_category') }} <x-store.icon name="arrow" :size="14" class="rtl:-scale-x-100" /></a>
                    </li>
                @empty
                    <li class="px-4 py-3 text-sm text-content-muted">{{ __('admin.storefront.none') }}</li>
                @endforelse
            </ol>
        </section>

        <p class="mt-3 flex items-start gap-2 text-xs text-content-muted"><x-store.icon name="info" :size="14" class="mt-0.5 shrink-0" /> {{ __('admin.storefront.footer_note') }}</p>

        {{-- Drawer (Figma 116:11898). --}}
        <div class="fixed inset-0 z-40 bg-ink-950/60" data-drawer-backdrop hidden></div>
        <aside class="fixed inset-y-0 end-0 z-50 flex w-full max-w-[480px] flex-col border-s border-line-subtle bg-surface-raised shadow-overlay" role="dialog" aria-modal="true" aria-labelledby="drawer-title" data-drawer hidden
            data-title-new="{{ __('admin.storefront.new') }}" data-title-edit="{{ __('admin.storefront.edit') }}" data-meta="{{ __('admin.storefront.drawer_meta', ['position' => '__position__', 'time' => '__time__']) }}" data-delete-confirm="{{ __('admin.storefront.delete_confirm') }}">
            <form class="flex min-h-0 flex-1 flex-col" novalidate data-drawer-form>
                <div class="flex items-start justify-between gap-3 border-b border-line-subtle px-6 py-5">
                    <div>
                        <h2 id="drawer-title" class="text-xl font-semibold" data-drawer-title>{{ __('admin.storefront.edit') }}</h2>
                        <p class="mt-1 text-sm text-content-muted" data-drawer-meta></p>
                    </div>
                    <button type="button" class="-me-2 inline-flex size-11 items-center justify-center rounded-md hover:bg-surface-sunken" data-drawer-close><x-store.icon name="x" :label="__('admin.storefront.cancel')" /></button>
                </div>

                <div class="flex flex-1 flex-col gap-5 overflow-y-auto px-6 py-5">
                    <x-admin.form-alerts />
                    <fieldset>
                        <legend class="mb-2 text-sm font-medium">{{ __('admin.storefront.section_type') }}</legend>
                        <div class="grid grid-cols-2 gap-1 rounded-md border border-line-default p-1 sm:grid-cols-4">
                            @foreach ($types as $type)
                                <label class="flex min-h-11 cursor-pointer items-center justify-center rounded-sm px-2 text-center text-xs font-medium has-checked:bg-accent-primary has-checked:text-content-on-accent has-focus-visible:outline-2 has-focus-visible:outline-focus-ring">
                                    <input type="radio" name="settings[type]" value="{{ $type }}" class="sr-only" data-type-option>
                                    {{ __('admin.storefront.types.'.$type) }}
                                </label>
                            @endforeach
                        </div>
                        <p class="field-error" data-error-for="settings.type" hidden></p>
                    </fieldset>

                    <div>
                        <label for="hs-title" class="field-label">{{ __('admin.storefront.title_en') }}</label>
                        <input id="hs-title" name="title" type="text" maxlength="190" class="admin-input" required>
                        <p class="field-error" data-error-for="title" hidden></p>
                    </div>
                    <div>
                        <label for="hs-title-ar" class="field-label text-end">{{ __('admin.storefront.title_ar') }}</label>
                        <input id="hs-title-ar" name="title_ar" type="text" maxlength="190" class="admin-input" dir="rtl" lang="ar">
                        <p class="field-error" data-error-for="title_ar" hidden></p>
                    </div>

                    <fieldset data-when-type="product_rail">
                        <legend class="mb-2 text-sm font-medium">{{ __('admin.storefront.rule') }}</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($rules as $rule)
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-md border border-line-default px-3 text-sm has-checked:border-accent-primary has-checked:bg-accent-tint">
                                    <input type="radio" name="settings[rule]" value="{{ $rule }}" class="size-4 accent-(--color-accent-text)" data-rule-option>
                                    {{ __('admin.storefront.rules.'.$rule) }}
                                </label>
                            @endforeach
                        </div>
                        <p class="field-error" data-error-for="settings.rule" hidden></p>
                    </fieldset>

                    <div data-when-rule="best_sellers">
                        <label for="hs-period" class="field-label">{{ __('admin.storefront.period') }}</label>
                        <select id="hs-period" name="settings[period]" class="admin-input">
                            @foreach (['all', '7d', '30d', '90d'] as $period)
                                <option value="{{ $period }}">{{ __('admin.storefront.periods.'.$period) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-when-rule="category">
                        <label for="hs-category" class="field-label">{{ __('admin.storefront.category') }}</label>
                        <select id="hs-category" name="settings[category]" class="admin-input">
                            <option value="">{{ __('admin.storefront.choose') }}</option>
                            @foreach ($categories as $slug => $label)
                                <option value="{{ $slug }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="field-error" data-error-for="settings.category" hidden></p>
                    </div>
                    <div data-when-rule="brand">
                        <label for="hs-brand" class="field-label">{{ __('admin.storefront.brand') }}</label>
                        <select id="hs-brand" name="settings[platform]" class="admin-input">
                            <option value="">{{ __('admin.storefront.choose') }}</option>
                            @foreach ($brands as $slug => $label)
                                <option value="{{ $slug }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="field-error" data-error-for="settings.platform" hidden></p>
                    </div>
                    <p class="text-xs text-content-muted" data-when-rule="manual">{{ __('admin.storefront.manual_note') }} <a href="#" class="text-accent-text underline" data-drawer-items>{{ __('admin.storefront.manual_items') }}</a></p>

                    <div data-when-type="category_tiles brand_logos product_rail">
                        <label for="hs-limit" class="field-label">{{ __('admin.storefront.item_limit') }}</label>
                        <input id="hs-limit" name="settings[limit]" type="number" min="4" max="24" class="admin-input" dir="ltr">
                        <p class="field-help">{{ __('admin.storefront.limit_help') }}</p>
                        <p class="field-error" data-error-for="settings.limit" hidden></p>
                    </div>

                    <div data-when-type="category_tiles brand_logos product_rail">
                        <p class="field-label flex items-center gap-2">{{ __('admin.storefront.see_all') }} <span class="rounded-xs bg-surface-sunken px-1.5 text-[11px]">{{ __('admin.storefront.auto') }}</span></p>
                        <p class="admin-input flex items-center gap-2 font-mono text-xs text-content-secondary" dir="ltr"><x-store.icon name="lock" :size="14" /> <span data-see-all>/products</span></p>
                        <p class="field-help">{{ __('admin.storefront.see_all_help') }}</p>
                    </div>

                    <label class="flex min-h-11 items-center gap-3 text-sm">
                        <input type="checkbox" name="is_active" class="size-5 accent-(--color-accent-text)" role="switch">
                        {{ __('admin.storefront.visible') }}
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-subtle px-6 py-4">
                    <button type="button" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-status-danger hover:underline" data-drawer-delete><x-store.icon name="trash" :size="16" /> {{ __('admin.storefront.delete') }}</button>
                    <div class="flex gap-2">
                        <button type="button" class="admin-btn min-h-11 px-5" data-drawer-close>{{ __('admin.storefront.cancel') }}</button>
                        <button type="submit" class="admin-btn-primary min-h-11 px-5">{{ __('admin.storefront.save') }}</button>
                    </div>
                </div>
            </form>
        </aside>
    </div>
@endsection
