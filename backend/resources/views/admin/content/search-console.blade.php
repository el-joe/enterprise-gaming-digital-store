{{-- Search Console report (M18, INTEGRATIONS.md section 7): totals, top queries and top landing pages from search_console_metrics. --}}
@extends('layouts.admin')

@section('title', __('admin.search_console.title'))
@section('heading', __('admin.nav.content'))

@section('content')
    @include('admin.partials.tabs', ['current' => 'search-console'])

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-content-secondary">{{ __('admin.search_console.intro') }}</p>
            <p class="mt-1 text-xs text-content-muted">
                <x-admin.badge :tone="$connected ? 'success' : 'neutral'" :dot="false">{{ $connected ? __('admin.search_console.connected') : __('admin.search_console.not_connected') }}</x-admin.badge>
                @if ($property)<span class="ms-2 font-mono" dir="ltr">{{ $property }}</span>@endif
                @if ($report['last_synced'])<span class="ms-2">{{ __('admin.search_console.last_synced', ['time' => $report['last_synced']]) }}</span>@endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <nav class="flex gap-1" aria-label="{{ __('admin.search_console.title') }}">
                @foreach ($windows as $window)
                    <a href="{{ route('admin.content.search-console', ['days' => $window]) }}" @class(['admin-btn min-h-9 px-3', 'admin-btn-primary' => $window === $days]) @if ($window === $days) aria-current="page" @endif>{{ __('admin.search_console.window', ['days' => $window]) }}</a>
                @endforeach
            </nav>
            @if ($connected)
                <div data-module="action">
                    <button type="button" class="admin-btn min-h-9" data-action-url="{{ url('/admin/api/seo/search-console/sync') }}" data-confirm-title="{{ __('admin.search_console.sync') }}"><x-store.icon name="refresh" :size="16" /> {{ __('admin.search_console.sync') }}</button>
                </div>
            @endif
        </div>
    </div>

    @if ($report['to'] === null)
        <div class="alert-warning mb-4" role="status"><x-store.icon name="info" class="mt-0.5" /><p>{{ __('admin.search_console.awaiting') }}</p></div>
    @else
        <p class="mb-4 text-xs text-content-muted">{{ __('admin.search_console.period', ['from' => $report['from'], 'to' => $report['to']]) }}</p>
    @endif

    <div class="mb-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4 md:mb-6">
        <x-admin.kpi :label="__('admin.search_console.clicks')" :value="number_format($report['totals']['clicks'])" />
        <x-admin.kpi :label="__('admin.search_console.impressions')" :value="number_format($report['totals']['impressions'])" />
        <x-admin.kpi :label="__('admin.search_console.ctr')" :value="$report['totals']['ctr'].'%'" />
        <x-admin.kpi :label="__('admin.search_console.position')" :value="$report['totals']['position']" />
    </div>

    <div class="grid gap-4 md:gap-6 xl:grid-cols-2">
        @foreach (['queries' => 'query', 'pages' => 'page'] as $list => $column)
            <x-admin.card :title="__('admin.search_console.'.$list)" :padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="admin-th">{{ __('admin.search_console.'.$column) }}</th>
                                <th class="admin-th text-end">{{ __('admin.search_console.clicks') }}</th>
                                <th class="admin-th text-end">{{ __('admin.search_console.impressions') }}</th>
                                <th class="admin-th text-end">{{ __('admin.search_console.ctr') }}</th>
                                <th class="admin-th text-end">{{ __('admin.search_console.position') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report[$list] as $row)
                                <tr>
                                    <td class="admin-td max-w-xs truncate" dir="auto" title="{{ $row['key'] }}">{{ $row['key'] }}</td>
                                    <td class="admin-td text-end" dir="ltr">{{ number_format($row['clicks']) }}</td>
                                    <td class="admin-td text-end" dir="ltr">{{ number_format($row['impressions']) }}</td>
                                    <td class="admin-td text-end" dir="ltr">{{ $row['ctr'] }}%</td>
                                    <td class="admin-td text-end" dir="ltr">{{ $row['position'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="admin-td text-center text-content-muted">{{ __('admin.search_console.none') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        @endforeach
    </div>

    <p class="mt-4 text-xs text-content-muted">{{ __('admin.search_console.limits') }}</p>
@endsection
