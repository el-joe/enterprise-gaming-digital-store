<ol class="mt-4 flex flex-wrap items-center gap-3 text-sm" aria-label="{{ __('storefront.checkout.steps') }}">
    @foreach (['contact', 'payment', 'done'] as $index => $step)
        @php($number = $index + 1)
        <li class="flex items-center gap-3" @if ($number === $current) aria-current="step" @endif>
            @unless ($loop->first)<span class="h-px w-6 bg-line-strong" aria-hidden="true"></span>@endunless
            <span @class([
                'inline-flex size-6 items-center justify-center rounded-full text-xs font-semibold',
                'bg-accent-primary text-content-on-accent' => $number <= $current,
                'bg-surface-overlay text-content-muted' => $number > $current,
            ])>
                @if ($number < $current)<x-store.icon name="check" :size="14" />@else{{ $number }}@endif
            </span>
            <span @class(['text-content-primary' => $number <= $current, 'text-content-muted' => $number > $current])>{{ __('storefront.checkout.step.'.$step) }}</span>
        </li>
    @endforeach
</ol>
