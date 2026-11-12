{{-- Category "Storefront placement" note (Figma 117:11830): computed "See all" and the home order. --}}
<div class="mt-4 flex flex-col gap-4">
    <div>
        <p class="field-label">{{ __('admin.storefront.see_all_auto') }}</p>
        <p class="admin-input flex items-center gap-2 font-mono text-xs text-content-secondary" dir="ltr"><x-store.icon name="lock" :size="14" /> {{ $aside['see_all'] }}</p>
    </div>
    <p class="flex gap-2 text-xs leading-5 text-content-muted"><x-store.icon name="info" :size="14" class="mt-0.5 shrink-0" /> {{ __('admin.storefront.home_note') }}</p>
    <div class="rounded-md border border-line-subtle bg-surface-sunken p-3 text-sm">
        <p class="mb-2 text-xs text-content-muted">{{ __('admin.storefront.home_order') }}</p>
        <ol class="flex flex-col gap-1">
            @if ($aside['managed'] > 0)
                <li><span class="font-mono" dir="ltr">1–{{ $aside['managed'] }}</span> {{ __('admin.storefront.managed_range') }}</li>
            @endif
            @foreach ($aside['rails'] as $index => $rail)
                <li @class(['text-accent-text font-medium' => $rail['id'] === $aside['current']])><span class="font-mono" dir="ltr">{{ $aside['managed'] + $index + 1 }}</span> {{ $rail['name'] }} @if ($rail['id'] === $aside['current']) {{ __('admin.storefront.this_category') }} @endif</li>
            @endforeach
        </ol>
    </div>
</div>
