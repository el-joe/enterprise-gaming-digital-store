{{--
    One delivered unit in a code list (Figma order detail "Digital codes" rows): the stored masked
    form only, never the code. `canReveal` shows the reveal button of the enclosing
    <x-store.code-reveal>; `revealUrl` overrides its URL template for this unit.
--}}
@props(['unit', 'canReveal' => false, 'revealUrl' => null, 'compact' => false])

@php
    $delivered = $unit['status'] === 'delivered';
    $masked = $unit['masked_code'] ? str_replace('*', '•', $unit['masked_code']) : null;
    $sealed = $delivered && empty($unit['first_revealed_at']);
@endphp

<div {{ $attributes->class(['flex flex-wrap items-center gap-3 rounded-md border border-line-subtle bg-surface-sunken p-3']) }} data-unit="{{ $unit['public_id'] }}">
    <x-store.icon :name="$delivered && ! $sealed ? 'eye' : 'lock'" :size="18" @class(['shrink-0', 'text-accent-text' => $delivered && ! $sealed, 'text-content-muted' => ! ($delivered && ! $sealed)]) />
    <div class="min-w-0 flex-1">
        @if ($masked)
            <p class="truncate font-mono text-sm tracking-[0.12em] text-content-secondary" dir="ltr" data-code-slot>{{ $masked }}</p>
        @endif
        <p class="text-xs text-content-muted">
            @if (! $delivered)
                {{ Lang::has('storefront.unit.'.$unit['status']) ? __('storefront.unit.'.$unit['status']) : \Illuminate\Support\Str::headline($unit['status']) }}
            @elseif ($sealed)
                {{ __('account.codes.sealed_hint') }}
            @else
                {{ __('account.codes.revealed_on') }} <x-account.time :at="$unit['first_revealed_at']" format="datetime" />
            @endif
        </p>
    </div>
    @if ($canReveal && ($unit['revealable'] ?? false))
        <button type="button" class="keycut-sm inline-flex h-9 shrink-0 items-center gap-1.5 bg-accent-primary px-3.5 text-sm font-semibold text-content-on-accent hover:bg-accent-hover"
            data-reveal="{{ $unit['public_id'] }}" data-sealed="{{ $sealed ? '1' : '0' }}" @if ($revealUrl) data-reveal-url="{{ $revealUrl }}" @endif>
            <x-store.icon name="eye" :size="16" /> {{ __('account.reveal.button') }}
        </button>
    @endif
    {{ $slot }}
</div>
