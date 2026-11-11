{{--
    An admin server-side table (M14, Figma "DataTables patterns"): toolbar (search, filters,
    saved filters, columns, export), bulk bar, the DataTables table, skeleton while loading,
    empty state with "clear filters", info, page length and numbered pager.
    `table` is the array from App\Http\Support\AdminTablePage::make; `search` an initial search;
    `actions` wires `[data-action-url]` buttons inside rows (admin/action.js, delegated).
--}}
@props(['table', 'search' => '', 'title' => null, 'actions' => false])

@php
    $config = [
        'columns' => $table['columns'],
        'order' => $table['order'],
        'bulk' => $table['bulk'],
        'context' => $table['context'],
        'preferences' => $table['preferences'],
        'urls' => $table['urls'],
    ];
    $hidden = $table['preferences']['hidden_columns'] ?? collect($table['columns'])->where('visible', false)->pluck('key')->all();
    $id = 'dt-'.$table['key'];
@endphp

<section {{ $attributes->class(['admin-card min-w-0']) }} data-module="{{ $actions ? 'table action' : 'table' }}" data-table-config="{{ json_encode($config) }}" aria-labelledby="{{ $id }}-title">
    <h2 id="{{ $id }}-title" class="sr-only">{{ $title ?? __('admin.table.title') }}</h2>

    <div class="flex flex-wrap items-center gap-2 p-4">
        @if ($table['searchable'])
            <div class="relative w-full sm:w-64">
                <x-store.icon name="search" :size="16" class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-content-muted" />
                <label for="{{ $id }}-search" class="sr-only">{{ __('admin.table.search') }}</label>
                <input id="{{ $id }}-search" type="search" maxlength="100" value="{{ $search }}" placeholder="{{ __('admin.table.search') }}" class="admin-input ps-9" data-dt-search>
            </div>
        @endif

        @foreach ($table['filters'] as $filter)
            <div class="w-[calc(50%-0.25rem)] sm:w-auto">
                <label for="{{ $id }}-f-{{ $filter['key'] }}" class="sr-only">{{ $filter['label'] }}</label>
                @if ($filter['type'] === 'select')
                    <select id="{{ $id }}-f-{{ $filter['key'] }}" class="admin-input pe-8 sm:w-auto" data-dt-filter="{{ $filter['key'] }}">
                        <option value="">{{ $filter['label'] }}: {{ __('admin.table.any') }}</option>
                        @foreach ($filter['options'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input id="{{ $id }}-f-{{ $filter['key'] }}" type="date" class="admin-input" data-dt-filter="{{ $filter['key'] }}" aria-label="{{ $filter['label'] }}" title="{{ $filter['label'] }}">
                @endif
            </div>
        @endforeach

        <div class="ms-auto flex flex-wrap items-center gap-2">
            <div class="relative" data-dt-menu>
                <button type="button" class="admin-btn" data-dt-menu-toggle aria-expanded="false" aria-haspopup="true">
                    <x-store.icon name="bookmark" :size="16" /> <span data-dt-saved-label>{{ __('admin.table.saved') }}</span>
                </button>
                <div class="admin-card absolute end-0 top-11 z-20 w-72 p-2 shadow-raised" data-dt-menu-panel hidden>
                    <p class="px-2 pt-1 pb-2 text-xs font-medium text-content-muted">{{ __('admin.table.saved_filters') }}</p>
                    <ul class="flex flex-col gap-0.5" data-dt-saved-list></ul>
                    <p class="px-2 py-1.5 text-sm text-content-muted" data-dt-saved-empty>{{ __('admin.table.saved_none') }}</p>
                    <form class="mt-2 border-t border-line-subtle px-2 pt-3" data-dt-saved-form novalidate>
                        <label for="{{ $id }}-saved-name" class="field-label">{{ __('admin.table.save_current') }}</label>
                        <div class="flex gap-2">
                            <input id="{{ $id }}-saved-name" name="name" maxlength="120" class="admin-input" placeholder="{{ __('admin.table.saved_name') }}" aria-describedby="{{ $id }}-saved-error">
                            <button type="submit" class="admin-btn-primary shrink-0">{{ __('admin.common.save') }}</button>
                        </div>
                        <label class="mt-2 flex items-center gap-2 text-xs text-content-secondary"><input type="checkbox" name="is_default" class="size-4 accent-(--color-accent-text)"> {{ __('admin.table.saved_default') }}</label>
                        <p id="{{ $id }}-saved-error" class="field-error" data-error-for="name" hidden></p>
                    </form>
                </div>
            </div>

            <div class="relative" data-dt-menu>
                <button type="button" class="admin-btn" data-dt-menu-toggle aria-expanded="false" aria-haspopup="true">
                    <x-store.icon name="columns" :size="16" /> {{ __('admin.table.columns') }}
                </button>
                <div class="admin-card absolute end-0 top-11 z-20 w-56 p-2 shadow-raised" data-dt-menu-panel hidden>
                    <p class="px-2 pt-1 pb-2 text-xs font-medium text-content-muted">{{ __('admin.table.toggle_columns') }}</p>
                    @foreach ($table['columns'] as $column)
                        <label class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-surface-sunken">
                            <input type="checkbox" value="{{ $column['key'] }}" class="size-4 accent-(--color-accent-text)" data-dt-column-toggle @checked(! in_array($column['key'], $hidden, true))>
                            {{ $column['label'] }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="relative" data-dt-menu>
                <button type="button" class="admin-btn" data-dt-menu-toggle aria-expanded="false" aria-haspopup="true">
                    <x-store.icon name="download" :size="16" /> {{ __('admin.table.export') }}
                </button>
                <div class="admin-card absolute end-0 top-11 z-20 w-48 p-2 shadow-raised" data-dt-menu-panel hidden>
                    <button type="button" class="w-full rounded-sm px-2 py-1.5 text-start text-sm hover:bg-surface-sunken" data-dt-export-format="csv">{{ __('admin.table.export_csv') }}</button>
                    <button type="button" class="w-full rounded-sm px-2 py-1.5 text-start text-sm hover:bg-surface-sunken" data-dt-export-format="xlsx">{{ __('admin.table.export_xlsx') }}</button>
                </div>
            </div>
        </div>
        <p class="w-full text-xs text-content-secondary" data-dt-export-status role="status" hidden></p>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-y border-accent-primary bg-accent-tint/50 px-4 py-2.5" data-dt-bulk hidden>
        <p class="me-auto text-sm font-semibold text-accent-text" data-dt-bulk-count></p>
        <button type="button" class="admin-btn" data-dt-export-format="csv" data-dt-export-selected>{{ __('admin.table.export_selected') }}</button>
        @foreach ($table['bulk'] as $action)
            <button type="button" class="{{ $action['danger'] ? 'admin-btn-danger' : 'admin-btn' }}" data-dt-bulk-action="{{ $action['key'] }}" @if ($table['key'] === 'reconciliation') data-needs-note @endif>{{ $action['label'] }}</button>
        @endforeach
    </div>

    <div class="alert-danger mx-4 mb-3" data-dt-error hidden role="alert"><x-store.icon name="alert" class="mt-0.5 text-status-danger" /><p data-dt-error-text></p></div>

    <div class="relative overflow-x-auto">
        <table class="w-full" data-dt-table>
            <thead>
                <tr>
                    <th class="w-10"><input type="checkbox" class="size-4 accent-(--color-accent-text)" data-dt-check-all aria-label="{{ __('admin.table.select_page') }}"></th>
                    @foreach ($table['columns'] as $column)
                        <th>{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
        </table>
        <div class="pointer-events-none absolute inset-x-0 top-10 flex flex-col gap-6 bg-surface-raised/80 px-4 py-5" data-dt-skeleton aria-hidden="true">
            @foreach (range(1, 4) as $row)
                <div class="flex gap-4"><span class="admin-skeleton w-20"></span><span class="admin-skeleton w-48"></span><span class="admin-skeleton w-28"></span><span class="admin-skeleton w-16"></span></div>
            @endforeach
        </div>
    </div>

    <div class="flex flex-col items-center gap-3 px-4 py-10 text-center" data-dt-empty hidden>
        <x-store.icon name="filter" :size="24" class="text-content-muted" />
        <p class="font-semibold">{{ __('admin.table.no_match_title') }}</p>
        <button type="button" class="admin-btn" data-dt-clear>{{ __('admin.table.clear_filters') }}</button>
    </div>

    <div class="flex flex-col gap-3 border-t border-line-subtle px-4 py-3 md:flex-row md:items-center md:justify-between">
        <p class="text-sm text-content-secondary" data-dt-info aria-live="polite"></p>
        <div class="flex flex-wrap items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-content-secondary">
                <span class="sr-only">{{ __('admin.table.per_page_label') }}</span>
                <select class="admin-input w-auto pe-8" data-dt-length>
                    @foreach ($table['page_lengths'] as $length)
                        <option value="{{ $length }}" @selected($length === $table['preferences']['page_length'])>{{ __('admin.table.per_page', ['count' => $length]) }}</option>
                    @endforeach
                </select>
            </label>
            <nav class="flex flex-wrap items-center gap-1.5" aria-label="{{ __('admin.table.pagination') }}" data-dt-pager></nav>
        </div>
    </div>
</section>
