{{-- A KPI card (Figma "Stat Card"): label, value, change against the previous period and an SVG sparkline. --}}
@props(['label', 'value', 'delta' => null, 'good' => 'up', 'note' => null, 'series' => []])

@php
    $points = '';
    $values = array_map('floatval', $series);

    if (count($values) > 1) {
        $max = max($values);
        $min = min($values);
        $span = $max - $min ?: 1;
        $step = 96 / (count($values) - 1);
        $points = implode(' ', array_map(static fn (int $i, float $v): string => round($i * $step, 1).','.round(30 - (($v - $min) / $span) * 26, 1), array_keys($values), $values));
    }

    $positive = $delta !== null && $delta['direction'] !== 'flat' && $delta['direction'] === $good;
    $negative = $delta !== null && $delta['direction'] !== 'flat' && $delta['direction'] !== $good;
@endphp

<div {{ $attributes->class(['admin-card flex items-start justify-between gap-3 p-4']) }}>
    <div class="min-w-0">
        <p class="text-xs text-content-secondary">{{ $label }}</p>
        <p class="mt-1 font-display text-[28px] leading-8 font-bold" dir="ltr">{{ $value }}</p>
        <p class="mt-1 text-xs text-content-muted">
            @if ($delta)
                <span @class(['font-semibold', 'text-status-success' => $positive, 'text-status-danger' => $negative])>{{ $delta['direction'] === 'up' ? '↑' : ($delta['direction'] === 'down' ? '↓' : '–') }} <span dir="ltr">{{ $delta['value'] }}</span></span>
                {{ __('admin.dashboard.vs_previous') }}
            @elseif ($note)
                {{ $note }}
            @else
                {{ __('admin.dashboard.no_previous') }}
            @endif
        </p>
    </div>
    @if ($points !== '')
        <svg viewBox="0 0 96 32" class="h-8 w-24 shrink-0 {{ $good === 'down' ? 'text-status-danger' : 'text-accent-text' }}" aria-hidden="true">
            <polyline points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
        </svg>
    @endif
</div>
