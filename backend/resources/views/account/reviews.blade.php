@extends('layouts.account')

@section('title', __('account.reviews.title'))
@section('nav', 'reviews')

@section('page')
    <x-account.page-head :title="__('account.reviews.title')" :subtitle="__('account.reviews.subtitle')" />

    @if ($eligible !== [])
        <section aria-labelledby="to-review" class="flex flex-col gap-3">
            <h2 id="to-review" class="text-xl font-semibold">{{ __('account.reviews.to_write') }}</h2>
            @unless ($verified)
                <p class="text-sm text-status-warning">{{ __('account.reviews.verify_first') }}</p>
            @endunless
            <ul class="flex flex-col gap-3">
                @foreach ($eligible as $item)
                    <li>
                        <details class="group rounded-xl border border-line-subtle bg-surface-raised p-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold">{{ $item['product']['name'] }}</span>
                                    <span class="text-xs text-content-muted">{{ __('account.codes.order') }} <span dir="ltr">{{ $item['order']['order_number'] }}</span></span>
                                </span>
                                <span class="btn-secondary min-h-9 shrink-0 px-3 text-xs"><x-store.icon name="pencil" :size="14" /> {{ __('account.reviews.write') }}</span>
                            </summary>
                            <div class="mt-4 border-t border-line-subtle pt-4">
                                <x-account.review-form :order-item-id="$item['order_item_id']" :id-prefix="'new-'.$item['order_item_id']" />
                            </div>
                        </details>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section aria-labelledby="my-reviews" class="flex flex-col gap-3">
        <h2 id="my-reviews" class="text-xl font-semibold">{{ __('account.reviews.mine') }}</h2>
        @if ($reviews === [])
            <x-store.empty-state art="no-reviews" :title="__('account.reviews.empty_title')" :text="__('account.reviews.empty_text')" class="rounded-xl border border-line-subtle bg-surface-raised" />
        @else
            <ul class="flex flex-col gap-3">
                @foreach ($reviews as $review)
                    <li class="rounded-xl rounded-se-[28px] border border-line-subtle bg-surface-raised p-4 md:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold">
                                    @if ($review['product']['slug'])
                                        <a href="{{ url()->localized('storefront.products.show', ['slug' => $review['product']['slug']]) }}" class="hover:text-accent-text">{{ $review['product']['name'] }}</a>
                                    @else
                                        {{ $review['product']['name'] }}
                                    @endif
                                </p>
                                <div class="mt-1 flex items-center gap-2 text-xs text-content-muted"><x-store.stars :rating="$review['rating']" /> <x-account.time :at="$review['created_at']" /></div>
                            </div>
                            <x-account.badge :tone="['approved' => 'success', 'rejected' => 'danger'][$review['status']] ?? 'warning'">{{ __('account.reviews.statuses.'.$review['status']) }}</x-account.badge>
                        </div>
                        @if ($review['title'])<p class="mt-3 font-medium">{{ $review['title'] }}</p>@endif
                        @if ($review['body'])<p class="mt-1 text-sm whitespace-pre-line text-content-secondary">{{ $review['body'] }}</p>@endif
                        <details class="group mt-3">
                            <summary class="inline-flex cursor-pointer list-none items-center gap-1 text-sm font-medium text-accent-text"><x-store.icon name="pencil" :size="14" /> {{ __('account.reviews.edit') }}</summary>
                            <div class="mt-3 border-t border-line-subtle pt-3">
                                <p class="mb-3 text-xs text-content-muted">{{ __('account.reviews.edit_note') }}</p>
                                <x-account.review-form :review="$review" :id-prefix="'edit-'.$review['id']" />
                            </div>
                        </details>
                    </li>
                @endforeach
            </ul>
            @include('account.partials.pagination', ['paginator' => $paginator])
        @endif
    </section>
@endsection
