{{-- Form-level error (Figma "fields need attention") and success messages for admin JSON forms. --}}
<div class="alert-danger" data-form-error hidden role="alert">
    <x-store.icon name="alert" class="mt-0.5 text-status-danger" />
    <div>
        <p class="font-semibold">{{ __('admin.form.attention') }}</p>
        <p data-form-error-text></p>
    </div>
</div>
<div class="alert-success" data-form-success hidden role="status">
    <x-store.icon name="check" class="mt-0.5 text-status-success" />
    <p data-form-success-text></p>
</div>
