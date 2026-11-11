{{--
    Write or edit a review (M13): rating 1 to 5, optional title and text. `review` null writes a
    new review for `orderItemId` (POST); otherwise it edits that review (PATCH), which returns it
    to moderation.
--}}
@props(['review' => null, 'orderItemId' => null, 'idPrefix'])

<form class="flex flex-col gap-3" data-module="account-form" data-success="reload" novalidate
    data-action="{{ $review ? url('/api/v1/account/reviews/'.$review['id']) : url('/api/v1/account/reviews') }}"
    data-method="{{ $review ? 'PATCH' : 'POST' }}">
    @unless ($review)
        <input type="hidden" name="order_item_id" value="{{ $orderItemId }}">
    @endunless
    <fieldset>
        <legend class="field-label">{{ __('account.reviews.rating') }}</legend>
        <div class="flex flex-wrap gap-2" dir="ltr">
            @for ($i = 1; $i <= 5; $i++)
                <label class="cursor-pointer">
                    <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked(($review['rating'] ?? 5) === $i) required>
                    <span class="inline-flex h-9 items-center gap-1 rounded-md border border-line-default px-3 text-sm peer-checked:border-accent-primary peer-checked:bg-accent-tint peer-checked:text-accent-text peer-focus-visible:outline-2 peer-focus-visible:outline-focus-ring">{{ $i }} <x-store.icon name="star" :size="14" class="fill-current text-sun-500" /></span>
                </label>
            @endfor
        </div>
        <p class="field-error" data-error-for="rating" hidden></p>
    </fieldset>
    <x-store.field name="title" :id="$idPrefix.'-title'" :label="__('account.reviews.review_title')" :value="$review['title'] ?? null" maxlength="190" />
    <div>
        <label for="{{ $idPrefix }}-body" class="field-label">{{ __('account.reviews.body') }}</label>
        <textarea id="{{ $idPrefix }}-body" name="body" rows="4" maxlength="5000" class="field-input py-2.5" aria-describedby="{{ $idPrefix }}-body-error">{{ $review['body'] ?? '' }}</textarea>
        <p id="{{ $idPrefix }}-body-error" class="field-error" data-error-for="body" hidden></p>
    </div>
    <p class="field-error" data-error-for="order_item_id" hidden></p>
    <div class="alert-danger" data-form-error hidden role="alert"><p data-form-error-text></p></div>
    <div><button type="submit" class="btn-primary">{{ $review ? __('account.reviews.save') : __('account.reviews.submit') }}</button></div>
</form>
