{{-- Section sub-navigation (M15): one tab per screen of the current section the staff member may open. --}}
@if (count($admin['tabs']) > 1)
    <nav class="mb-4 flex gap-1 overflow-x-auto border-b border-line-subtle md:mb-6" aria-label="{{ __('admin.nav.'.$admin['section']) }}">
        @foreach ($admin['tabs'] as $item)
            @php($active = $item['key'] === $current)
            <a href="{{ $item['url'] }}" @class([
                'shrink-0 border-b-2 px-3 py-2.5 text-sm font-medium whitespace-nowrap',
                'border-accent-primary text-content-primary' => $active,
                'border-transparent text-content-secondary hover:text-content-primary' => ! $active,
            ]) @if ($active) aria-current="page" @endif>{{ __('admin.tabs.'.$admin['section'].'.'.$item['key']) }}</a>
        @endforeach
    </nav>
@endif
