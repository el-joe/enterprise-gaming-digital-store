<?php

namespace App\DataTables;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * HTML for the raw (`html`) cells of admin tables. Every value is escaped here; only the markup
 * of these helpers reaches the browser unescaped. Classes match the Blade badge component and
 * are scanned by Tailwind (`@source` in app.css). No inline styles (strict CSP).
 */
final class Cell
{
    private const array TONES = [
        'success' => 'bg-status-success-tint text-status-success',
        'info' => 'bg-status-info-tint text-status-info',
        'warning' => 'bg-status-warning-tint text-status-warning',
        'danger' => 'bg-status-danger-tint text-status-danger',
        'accent' => 'bg-accent-tint text-accent-text',
        'neutral' => 'bg-surface-sunken text-content-secondary',
    ];

    public static function badge(string $label, string $tone = 'neutral', bool $dot = true): string
    {
        $classes = self::TONES[$tone] ?? self::TONES['neutral'];
        $dotHtml = $dot ? '<span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>' : '';

        return '<span class="inline-flex items-center gap-1.5 rounded-xs px-2 py-0.5 text-xs leading-5 font-medium whitespace-nowrap '.$classes.'">'.$dotHtml.e($label).'</span>';
    }

    /**
     * A status badge for an enum value of a group in `admin.enums` (label) and `admin.tones` (tone).
     */
    public static function status(string $group, ?string $value): string
    {
        if ($value === null || $value === '') {
            return self::muted('—');
        }

        return self::badge(self::label($group, $value), self::tone($group, $value));
    }

    public static function label(string $group, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $key = "admin.enums.{$group}.{$value}";

        return Lang::has($key) ? (string) __($key) : Str::headline($value);
    }

    public static function tone(string $group, string $value): string
    {
        return (string) (config("admin.tones.{$group}.{$value}") ?? 'neutral');
    }

    public static function link(string $url, string $text, bool $mono = false): string
    {
        $classes = $mono ? 'font-mono text-xs text-content-primary underline-offset-2 hover:underline' : 'font-medium text-content-primary underline-offset-2 hover:underline';

        return '<a href="'.e($url).'" class="'.$classes.'">'.e($text).'</a>';
    }

    public static function stack(string $primary, ?string $secondary): string
    {
        $second = $secondary === null || $secondary === '' ? '' : '<span class="block text-xs text-content-muted">'.e($secondary).'</span>';

        return '<span class="block text-content-primary">'.e($primary).'</span>'.$second;
    }

    public static function mono(?string $text): string
    {
        return $text === null || $text === '' ? self::muted('—') : '<span class="font-mono text-xs text-content-secondary">'.e($text).'</span>';
    }

    public static function muted(string $text): string
    {
        return '<span class="text-content-muted">'.e($text).'</span>';
    }

    public static function strong(string $text): string
    {
        return '<span class="font-semibold whitespace-nowrap text-content-primary" dir="ltr">'.e($text).'</span>';
    }

    /**
     * A row action button (M15) for `admin/action.js` (delegated inside the table): the JSON
     * route, its method and body, the confirmation title and, with `reason`, a required written
     * reason sent as `reasonField`. Every attribute is escaped.
     *
     * @param  array<string, mixed>  $body
     */
    public static function action(
        string $label,
        string $url,
        string $method = 'POST',
        array $body = [],
        ?string $confirm = null,
        bool $danger = false,
        ?string $reason = null,
        string $reasonField = 'reason',
    ): string {
        $attributes = [
            'type' => 'button',
            'class' => $danger ? 'admin-btn h-8 text-status-danger' : 'admin-btn h-8',
            'data-action-url' => $url,
            'data-action-method' => $method,
            'data-confirm-title' => $confirm ?? $label,
        ];

        if ($body !== []) {
            $attributes['data-action-body'] = (string) json_encode($body);
        }

        if ($reason !== null) {
            $attributes['data-reason'] = $reason;
            $attributes['data-reason-field'] = $reasonField;
        }

        $html = '';

        foreach ($attributes as $name => $value) {
            $html .= ' '.$name.'="'.e($value).'"';
        }

        return '<button'.$html.($danger ? ' data-confirm-danger' : '').'>'.e($label).'</button>';
    }

    /**
     * Several row actions side by side.
     */
    public static function actions(string ...$buttons): string
    {
        return '<span class="flex flex-wrap justify-end gap-1.5">'.implode('', $buttons).'</span>';
    }

    public static function yesNo(bool $value): string
    {
        return $value
            ? self::badge((string) __('admin.common.yes'), 'success', false)
            : self::badge((string) __('admin.common.no'), 'neutral', false);
    }
}
