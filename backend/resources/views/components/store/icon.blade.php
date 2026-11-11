{{--
    Line icons of the design system (24 grid, 2px stroke, currentColor). Decorative by default;
    pass `label` for a meaningful icon. Directional icons (arrow, chevron-forward) mirror in RTL.
--}}
@props(['name', 'size' => 20, 'label' => null])

@php
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'bag' => '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'heart' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-forward' => '<path d="m9 6 6 6-6 6"/>',
        'chevron-back' => '<path d="m15 6-6 6 6 6"/>',
        'zap' => '<path d="M13 3 5 13h6l-1 8 8-10h-6l1-8Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check' => '<path d="m5 12 5 5 9-10"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'alert' => '<path d="M12 4 2.5 20h19L12 4Z"/><path d="M12 10v4M12 17h.01"/>',
        'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'minus' => '<path d="M5 12h14"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8L3 12Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'grid' => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        'gamepad' => '<rect x="2" y="7" width="20" height="11" rx="4"/><path d="M7 11v3M5.5 12.5h3M15 12h.01M18 13h.01"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'pause' => '<path d="M8 5v14M16 5v14"/>',
        'play' => '<path d="m7 5 12 7-12 7V5Z"/>',
        'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
        'logout' => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11"/>',
        // M13 account icons
        'box' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
        'key' => '<circle cx="7.5" cy="15.5" r="3.5"/><path d="m10 13 9-9M16 7l2 2M14 9l1.5 1.5"/>',
        'wallet' => '<path d="M4 7a2 2 0 0 1 2-2h12v4"/><rect x="4" y="9" width="17" height="10" rx="2"/><path d="M16 14h.01"/>',
        'chat' => '<path d="M4 5h16v11H9l-5 4V5Z"/>',
        'bell' => '<path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15L6 16Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'send' => '<path d="M21 3 3 10.5l7 2.5 2.5 7L21 3Z"/><path d="m10 13 4-4"/>',
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 6.1A10 10 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.2 3.9M6.6 6.6A17 17 0 0 0 2 12s3.5 6 10 6a9.6 9.6 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'phone' => '<rect x="7" y="2.5" width="10" height="19" rx="2"/><path d="M11 18h2"/>',
        'monitor' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M9 20h6M12 16v4"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
        'pencil' => '<path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/>',
        // M14 admin icons
        'plug' => '<path d="M9 3v5M15 3v5M6 8h12v3a6 6 0 0 1-12 0V8ZM12 17v4"/>',
        'percent' => '<path d="M19 5 5 19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
        'file' => '<path d="M6 3h9l4 4v14H6V3Z"/><path d="M9 11h7M9 15h7M9 7h3"/>',
        'sliders' => '<path d="M5 4v16M12 4v16M19 4v16"/><path d="M3 9h4M10 15h4M17 7h4"/>',
        'refresh' => '<path d="M20 11a8 8 0 0 0-14.7-4.3L4 8M4 4v4h4M4 13a8 8 0 0 0 14.7 4.3L20 16M20 20v-4h-4"/>',
        'download' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
        'columns' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M12 4v16"/>',
        'bookmark' => '<path d="M6 3h12v18l-6-4-6 4V3Z"/>',
        'activity' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'panel' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/>',
        'print' => '<path d="M7 9V3h10v6M7 17H4v-7h16v7h-3M7 14h10v7H7v-7Z"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6V14M12 17h.01"/>',
    ];
    $mirrored = in_array($name, ['arrow', 'chevron-forward', 'chevron-back', 'logout', 'send', 'chat'], true);
@endphp

<svg
    {{ $attributes->class(['shrink-0', 'rtl:-scale-x-100' => $mirrored]) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" focusable="false" @endif
>{!! $paths[$name] ?? '' !!}</svg>
