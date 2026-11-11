{{-- An enum value as a badge: label from lang admin.enums.{group}, tone from config admin.tones.{group}. --}}
@props(['group', 'value', 'dot' => true])

@if ($value === null || $value === '')
    <span class="text-content-muted">—</span>
@else
    <x-admin.badge :tone="\App\DataTables\Cell::tone($group, (string) $value)" :dot="$dot" {{ $attributes }}>{{ \App\DataTables\Cell::label($group, (string) $value) }}</x-admin.badge>
@endif
