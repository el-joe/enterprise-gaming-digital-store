{{--
    An admin form field whose JSON envelope errors (dot keys) appear beside it
    (resources/js/lib/forms.js `[data-error-for]`). `errorKey` defaults to the dot form of `name`.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'errorKey' => null, 'id' => null])

@php
    $id ??= 'f-'.preg_replace('/[^a-z0-9]+/i', '-', $name);
    $key = $errorKey ?? str_replace(['][', '[', ']'], ['.', '.', ''], $name);
@endphp

<div>
    <label for="{{ $id }}" class="field-label">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" aria-describedby="{{ $id }}-error{{ $help ? ' '.$id.'-help' : '' }}" {{ $attributes->class(['admin-input']) }}>
    @if ($help)<p id="{{ $id }}-help" class="field-help">{{ $help }}</p>@endif
    <p id="{{ $id }}-error" class="field-error" data-error-for="{{ $key }}" hidden></p>
</div>
