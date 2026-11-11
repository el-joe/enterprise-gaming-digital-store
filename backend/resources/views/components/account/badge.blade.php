{{-- A small status pill with a dot (Figma "Status Badge"). Tones: success, info, warning, danger, accent, neutral. --}}
@props(['tone' => 'neutral', 'dot' => true])

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-xs px-2 py-0.5 text-xs leading-5 font-medium whitespace-nowrap',
    'bg-status-success-tint text-status-success' => $tone === 'success',
    'bg-status-info-tint text-status-info' => $tone === 'info',
    'bg-status-warning-tint text-status-warning' => $tone === 'warning',
    'bg-status-danger-tint text-status-danger' => $tone === 'danger',
    'bg-accent-tint text-accent-text' => $tone === 'accent',
    'bg-surface-overlay text-content-secondary' => $tone === 'neutral',
]) }}>@if ($dot)<span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>@endif{{ $slot }}</span>
