{{-- One facet value: a checkbox (`name[]`), its label and its result count. --}}
@php $id = 'f-'.$name.'-'.preg_replace('/[^a-z0-9]+/i', '-', $option['value']); @endphp
<li data-facet-option data-label="{{ mb_strtolower($option['label']) }}">
    <label for="{{ $id }}" class="flex min-h-11 cursor-pointer items-center gap-3 text-sm">
        <input id="{{ $id }}" type="checkbox" name="{{ $name }}[]" value="{{ $option['value'] }}" class="size-5 shrink-0 rounded-xs accent-(--color-accent-primary)" @checked($option['selected']) data-auto-submit>
        <span class="min-w-0 flex-1 truncate">{{ $option['label'] }}</span>
        <span class="font-mono text-xs text-content-muted" dir="ltr">{{ number_format($option['count']) }}</span>
    </label>
</li>
