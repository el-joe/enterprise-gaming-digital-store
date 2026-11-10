{{-- Coupon form: `POST`/`DELETE /api/v1/storefront/cart/coupon`; `coupon_invalid` shows its message here. --}}
<div data-coupon>
    <label for="coupon-code" class="field-label">
        @if ($coupon && $coupon['applied'])
            {{ __('storefront.coupon.applied', ['code' => $coupon['code']]) }}
        @else
            {{ __('storefront.coupon.label') }}
        @endif
    </label>
    <div class="flex gap-2">
        <input id="coupon-code" name="code" type="text" maxlength="64" autocomplete="off" dir="ltr" value="{{ $coupon['code'] ?? '' }}"
            @class(['field-input uppercase', 'border-status-success' => $coupon && $coupon['applied']]) @readonly($coupon !== null) aria-describedby="coupon-error">
        @if ($coupon)
            <button type="button" class="btn-secondary" data-coupon-remove>{{ __('storefront.coupon.remove') }}</button>
        @else
            <button type="button" class="btn-secondary" data-coupon-apply>{{ __('storefront.coupon.apply') }}</button>
        @endif
    </div>
    <p id="coupon-error" class="field-error" data-error-for="code" @if (! $coupon || ! $coupon['error']) hidden @endif>{{ $coupon['error'] ?? '' }}</p>
</div>
