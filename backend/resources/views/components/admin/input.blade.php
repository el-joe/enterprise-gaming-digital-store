{{--
    One admin form field from App\Http\Support\AdminScreens\Field (M15): label, control, help and
    the envelope error (`[data-error-for]` with the dot key). `record` is the model being edited
    (null on create); `disabled` renders read-only for staff without the manage permission.
--}}
@props(['field', 'record' => null, 'disabled' => false, 'idPrefix' => 'f'])

@php
    $id = $idPrefix.'-'.preg_replace('/[^a-z0-9]+/i', '-', $field->name);
    $name = $field->inputName();
    $value = $field->valueFor($record);
    $describedBy = $id.'-error'.($field->help !== '' ? ' '.$id.'-help' : '');
@endphp

@if ($field->type === 'image')
    <x-admin.image-upload :name="$name" :value="$value" :label="$field->label" :help="$field->help ?: null" :required="$field->required" :disabled="$disabled" :id="$id" :error-key="$field->errorKey()" @class(['md:col-span-2' => $field->wide]) />
@else
<div @class(['md:col-span-2' => $field->wide])>
    @if ($field->type === 'toggle')
        <label class="flex min-h-10 items-center gap-3 text-sm">
            <input id="{{ $id }}" type="checkbox" name="{{ $name }}" class="size-5 accent-(--color-accent-text)" @checked($value) @disabled($disabled) aria-describedby="{{ $describedBy }}">
            <span>{{ $field->label }}</span>
        </label>
    @elseif ($field->type === 'multi')
        <fieldset aria-describedby="{{ $describedBy }}">
            <legend class="field-label">{{ $field->label }}</legend>
            <div class="grid max-h-56 gap-1 overflow-y-auto rounded-md border border-line-subtle p-2 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($field->options as $optionValue => $optionLabel)
                    <label class="flex items-center gap-2 rounded-sm px-2 py-1 text-sm hover:bg-surface-sunken">
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $optionValue }}" class="size-4 accent-(--color-accent-text)" @checked(in_array((string) $optionValue, $value, true)) @disabled($disabled)>
                        <span class="min-w-0 truncate">{{ $optionLabel }}</span>
                    </label>
                @empty
                    <p class="px-2 py-1 text-sm text-content-muted">{{ __('admin.common.none') }}</p>
                @endforelse
            </div>
            {{-- An empty list still sends the key, so every box can be cleared. --}}
            <input type="hidden" name="{{ $name }}[]" value="" data-list-sentinel>
        </fieldset>
    @else
        <label for="{{ $id }}" class="field-label">{{ $field->label }}@if ($field->required)<span class="text-status-danger" aria-hidden="true"> *</span>@endif</label>
        @if ($field->type === 'select')
            <select id="{{ $id }}" name="{{ $name }}" class="admin-input" @disabled($disabled) @if ($field->nullable) data-nullable @endif aria-describedby="{{ $describedBy }}">
                @if ($field->nullable || ! $field->required)
                    <option value="">{{ __('admin.common.none_selected') }}</option>
                @endif
                @foreach ($field->options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @elseif (in_array($field->type, ['textarea', 'html', 'json'], true))
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field->type === 'html' ? 12 : ($field->type === 'json' ? 6 : 4) }}" @class(['admin-input py-2', 'font-mono text-xs' => in_array($field->type, ['json', 'html'], true)]) @if ($field->maxLength) maxlength="{{ $field->maxLength }}" @endif @if ($field->ltr) dir="ltr" @endif @if ($field->nullable) data-nullable @endif @if ($field->type === 'json') data-json @endif @disabled($disabled) aria-describedby="{{ $describedBy }}">{{ $value }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $name }}"
                type="{{ match ($field->type) { 'number' => 'number', 'datetime' => 'datetime-local', 'password' => 'password', default => 'text' } }}"
                value="{{ $value }}"
                class="admin-input"
                @if ($field->type === 'decimal') inputmode="decimal" @endif
                @if ($field->type === 'password') autocomplete="new-password" @endif
                @if ($field->maxLength) maxlength="{{ $field->maxLength }}" @endif
                @if ($field->ltr) dir="ltr" @endif
                @if ($field->nullable) data-nullable @endif
                @if ($field->type === 'csv') data-csv @endif
                @disabled($disabled)
                aria-describedby="{{ $describedBy }}">
        @endif
    @endif
    @if ($field->help !== '')<p id="{{ $id }}-help" class="field-help">{{ $field->help }}</p>@endif
    <p id="{{ $id }}-error" class="field-error" data-error-for="{{ $field->errorKey() }}" hidden></p>
</div>
@endif
