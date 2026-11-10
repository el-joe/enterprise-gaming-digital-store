{{--
    A form field whose JSON errors (envelope `errors`, dot keys) appear in the [data-error-for]
    element beside it (resources/js/lib/forms.js).
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'id' => null])

@php($fieldId = $id ?? 'f-'.str_replace(['.', '[', ']'], '-', $name))

<div>
    <label for="{{ $fieldId }}" class="field-label">{{ $label }}</label>
    <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" aria-describedby="{{ $fieldId }}-error{{ $help ? ' '.$fieldId.'-help' : '' }}" {{ $attributes->class(['field-input']) }}>
    @if ($help)<p id="{{ $fieldId }}-help" class="field-help">{{ $help }}</p>@endif
    <p id="{{ $fieldId }}-error" class="field-error" data-error-for="{{ $name }}" hidden></p>
</div>
