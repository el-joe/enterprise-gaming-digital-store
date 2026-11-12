{{--
    Image field of the admin forms (owner request, owner decision 12): a file input with drag and
    drop, a preview of the current image, Replace and Remove. The file goes to the admin media
    upload (`/admin/api/media/images`: GD decode, AVIF and WebP, resized, metadata stripped, no SVG,
    5 MB) and the returned media id fills the hidden input, so the form's save API is unchanged.
    `value` is the current media id. Errors appear beside the field (`[data-error-for]`).
--}}
@props(['name', 'value' => null, 'label' => null, 'help' => null, 'required' => false, 'disabled' => false, 'id' => null, 'errorKey' => null])

@php
    $id ??= 'img-'.preg_replace('/[^a-z0-9]+/i', '-', $name);
    $errorKey ??= str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $media = filled($value) ? \App\Domains\Cms\Models\Media::query()->find((int) $value) : null;
    $preview = $media === null ? null : app(\App\Domains\Cms\Services\MediaUrls::class)->url((string) $media->disk, (string) $media->path);
    $maxBytes = \App\Domains\Cms\Services\ImageOptimizer::maxBytes();
@endphp

<div {{ $attributes->class(['flex flex-col gap-2']) }} data-module="image-upload"
    data-upload-url="{{ url('/admin/api/media/images') }}" data-max-bytes="{{ $maxBytes }}">
    @if ($label)
        <span class="field-label" id="{{ $id }}-label">{{ $label }}@if ($required)<span class="text-status-danger" aria-hidden="true"> *</span>@endif</span>
    @endif
    <input type="hidden" name="{{ $name }}" value="{{ $media?->id }}" data-nullable data-image-value @disabled($disabled)>
    <div class="relative flex min-h-28 items-center justify-center overflow-hidden rounded-md border border-dashed border-line-strong bg-surface-sunken p-2 text-center text-sm text-content-muted data-[dragging]:border-accent-primary" data-image-drop>
        <img src="{{ $preview ?? '' }}" alt="" class="max-h-40 w-auto rounded-sm object-contain" data-image-preview @if (! $preview) hidden @endif>
        <span data-image-empty @if ($preview) hidden @endif>{{ __('admin.image_upload.empty') }}</span>
        <span class="absolute inset-0 flex items-center justify-center bg-surface-raised/80" data-image-busy hidden>{{ __('admin.image_upload.uploading') }}</span>
    </div>
    @unless ($disabled)
        <div class="flex flex-wrap items-center gap-2">
            <label class="admin-btn h-9 cursor-pointer" for="{{ $id }}">
                <x-store.icon name="plus" :size="16" />
                <span data-image-choose @if ($preview) hidden @endif>{{ __('admin.image_upload.choose') }}</span>
                <span data-image-replace @if (! $preview) hidden @endif>{{ __('admin.image_upload.replace') }}</span>
            </label>
            <input id="{{ $id }}" type="file" class="sr-only" accept="image/png,image/jpeg,image/webp,image/avif" data-image-file
                aria-describedby="{{ $id }}-error{{ $help ? ' '.$id.'-help' : '' }}" @if ($label) aria-labelledby="{{ $id }}-label" @endif>
            <button type="button" class="admin-btn h-9 text-status-danger" data-image-remove @if (! $preview) hidden @endif>{{ __('admin.image_upload.remove') }}</button>
        </div>
    @endunless
    @if ($help)<p id="{{ $id }}-help" class="field-help">{{ $help }}</p>@endif
    <p id="{{ $id }}-error" class="field-error" data-error-for="{{ $errorKey }}" data-image-error hidden></p>
</div>
