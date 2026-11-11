{{-- A date from an ISO 8601 string in the page language, Western digits (owner decision 2). --}}
@props(['at', 'format' => 'date'])

@if ($at)
    @php($time = \Carbon\CarbonImmutable::parse($at)->setTimezone(config('app.timezone'))->locale(app()->getLocale()))
    <time datetime="{{ $time->toIso8601String() }}" {{ $attributes }}>{{ match ($format) {
        'datetime' => $time->isoFormat(__('account.formats.datetime')),
        'relative' => $time->diffForHumans(),
        default => $time->isoFormat(__('account.formats.date')),
    } }}</time>
@endif
