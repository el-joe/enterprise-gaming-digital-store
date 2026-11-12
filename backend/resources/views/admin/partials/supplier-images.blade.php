{{--
    Supplier images of a product, platform or category (owner request 2026-11-12): one thumbnail
    per supplier candidate with the supplier, size and date, an "Active" badge on the image in use
    and "Use this" (POST, admin/action.js, page reloads) for staff who may edit the record. The
    chosen image is then marked as chosen by staff, so no supplier sync replaces it.
    Expects `$candidates` (SupplierImageCandidate with media and supplier), `$activeMediaId`,
    `$canManage`.
--}}
@php
    $mediaUrls = app(\App\Domains\Cms\Services\MediaUrls::class);
@endphp

<x-admin.card :title="__('admin.supplier_images.title')" :subtitle="__('admin.supplier_images.note')">
    @if ($candidates->isEmpty())
        <p class="text-sm text-content-muted">{{ __('admin.supplier_images.empty') }}</p>
    @else
        <ul class="grid grid-cols-2 gap-3" data-module="action">
            @foreach ($candidates as $candidate)
                @php
                    $media = $candidate->media;
                    $formats = (array) ($media?->conversions ?? []);
                    $isActive = $media !== null && $media->id === $activeMediaId;
                @endphp
                <li @class(['flex flex-col gap-2 rounded-md border p-2', 'border-accent-primary' => $isActive, 'border-line-subtle' => ! $isActive])>
                    @if ($media)
                        <picture>
                            @foreach (['avif', 'webp'] as $format)
                                @if (! empty($formats[$format.'_480'] ?? $formats[$format] ?? null))<source type="image/{{ $format }}" srcset="{{ $mediaUrls->url((string) $media->disk, (string) ($formats[$format.'_480'] ?? $formats[$format])) }}">@endif
                            @endforeach
                            <img src="{{ $mediaUrls->url((string) $media->disk, (string) $media->path) }}" alt="{{ __('admin.supplier_images.alt', ['supplier' => $candidate->supplier?->name ?? '—']) }}"
                                class="aspect-video w-full rounded-sm bg-surface-sunken object-contain" loading="lazy" decoding="async">
                        </picture>
                    @endif
                    <p class="flex flex-wrap items-center gap-1 text-xs">
                        <span class="font-medium text-content-primary">{{ $candidate->supplier?->name ?? '—' }}</span>
                        @if ($isActive)<x-admin.badge tone="success">{{ __('admin.supplier_images.active') }}</x-admin.badge>@endif
                    </p>
                    <p class="text-xs text-content-muted" dir="ltr">
                        @if ($media?->width){{ $media->width }}×{{ $media->height }} · @endif{{ $candidate->fetched_at?->format('Y-m-d') ?? '—' }}
                    </p>
                    @if ($canManage && ! $isActive && $media)
                        <button type="button" class="admin-btn h-8" data-action-url="{{ route('admin.api.supplier-image-candidates.activate', ['supplierImageCandidate' => $candidate->id]) }}"
                            data-confirm-title="{{ __('admin.supplier_images.use_confirm', ['supplier' => $candidate->supplier?->name ?? '—']) }}">{{ __('admin.supplier_images.use') }}</button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-admin.card>
