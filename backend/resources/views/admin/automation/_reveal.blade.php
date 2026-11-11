{{-- One-time display of a new token or secret (M15). Filled from the issue response only; never stored or shown again. --}}
<div id="{{ $id }}" class="alert-warning flex-col" hidden role="status" tabindex="-1">
    <p class="font-semibold">{{ __('admin.automation.shown_once') }}</p>
    @foreach ($fields as $field => $label)
        <p class="mt-2 text-xs text-content-secondary">{{ $label }}</p>
        <code class="block rounded-sm bg-surface-sunken px-2 py-1.5 font-mono text-xs break-all text-content-primary select-all" dir="ltr" data-reveal-field="{{ $field }}"></code>
    @endforeach
</div>
