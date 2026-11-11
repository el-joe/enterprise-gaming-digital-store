{{-- A server-drawn bar chart (no chart library): geometry from AdminDashboardService::chart; the table below is the accessible form. --}}
<x-admin.card :title="$title" :subtitle="$subtitle">
    @if ($chart['empty'])
        <p class="py-10 text-center text-sm text-content-muted">{{ __('admin.dashboard.no_sales') }}</p>
    @else
        <div class="flex gap-2" dir="ltr">
            <div class="flex h-48 w-16 shrink-0 flex-col justify-between text-end text-[11px] text-content-muted" aria-hidden="true">
                @foreach ($chart['ticks'] as $tick)<span>{{ $tick['label'] }}</span>@endforeach
            </div>
            <svg viewBox="0 0 600 200" preserveAspectRatio="none" class="h-48 min-w-0 flex-1" role="img" aria-label="{{ $title }}">
                <line x1="0" y1="10" x2="600" y2="10" class="stroke-line-default" stroke-width="1" vector-effect="non-scaling-stroke" />
                <line x1="0" y1="105" x2="600" y2="105" class="stroke-line-default" stroke-width="1" vector-effect="non-scaling-stroke" />
                <line x1="0" y1="200" x2="600" y2="200" class="stroke-line-strong" stroke-width="1" vector-effect="non-scaling-stroke" />
                @foreach ($chart['bars'] as $bar)
                    <rect x="{{ $bar['x'] }}" y="{{ $bar['y'] }}" width="{{ $bar['w'] }}" height="{{ $bar['h'] }}" rx="2" class="{{ $tone }}"><title>{{ $bar['label'] }}: {{ $bar['value'] }}</title></rect>
                @endforeach
            </svg>
        </div>
        <div class="mt-2 flex justify-between ps-[72px] text-[11px] text-content-muted" dir="ltr" aria-hidden="true">
            <span>{{ $chart['bars'][0]['label'] ?? '' }}</span><span>{{ $chart['bars'][count($chart['bars']) - 1]['label'] ?? '' }}</span>
        </div>
        <details class="mt-3 text-xs text-content-secondary">
            <summary class="cursor-pointer">{{ __('admin.dashboard.chart_table') }}</summary>
            <table class="mt-2 w-full"><tbody>
                @foreach ($chart['bars'] as $bar)<tr><td class="py-0.5">{{ $bar['label'] }}</td><td class="py-0.5 text-end" dir="ltr">{{ $bar['value'] }}</td></tr>@endforeach
            </tbody></table>
        </details>
    @endif
</x-admin.card>
