{{-- Payments sub-navigation: one tab per screen the staff member may open. --}}
<nav class="mb-4 flex gap-1 overflow-x-auto border-b border-line-subtle md:mb-6" aria-label="{{ __('admin.payments.tabs') }}">
    @foreach ($admin['payment_tabs'] as $item)
        @php($active = $item['key'] === $current)
        <a href="{{ $item['url'] }}" @class([
            'shrink-0 border-b-2 px-3 py-2.5 text-sm font-medium whitespace-nowrap',
            'border-accent-primary text-content-primary' => $active,
            'border-transparent text-content-secondary hover:text-content-primary' => ! $active,
        ]) @if ($active) aria-current="page" @endif>{{ __('admin.payments.tab.'.$item['key']) }}</a>
    @endforeach
</nav>
