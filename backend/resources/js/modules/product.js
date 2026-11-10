import { config, t } from '../lib/config.js';
import { post } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, toast } from '../lib/forms.js';

/**
 * Product buy box: region and amount selection (prices are the server-formatted strings in the
 * buttons' data attributes), quantity, delivery fields, add to bag and buy now through
 * `POST /api/v1/storefront/cart/items`. Field errors from the envelope appear beside the fields.
 */
export default function product(form) {
    const variantInput = form.querySelector('[data-variant-input]');
    const variantButtons = Array.from(form.querySelectorAll('[data-variant-option]'));
    const regionButtons = Array.from(form.querySelectorAll('[data-region-option]'));
    const priceLabels = Array.from(document.querySelectorAll('[data-variant-price]'));
    const skuLabel = form.querySelector('[data-variant-sku]');
    const actions = [form.querySelector('[data-add-button]'), form.querySelector('[data-buy-button]'), document.querySelector('[data-sticky-buy]')].filter(Boolean);
    let intent = 'add';

    const selectVariant = (button) => {
        variantButtons.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
        variantInput.value = button && !button.disabled ? button.dataset.variantOption : '';
        priceLabels.forEach((label) => { label.textContent = button?.dataset.price || '—'; });

        if (skuLabel && button) {
            skuLabel.textContent = button.dataset.sku;
        }

        actions.forEach((action) => { action.disabled = variantInput.value === ''; });
    };

    regionButtons.forEach((regionButton) => regionButton.addEventListener('click', () => {
        const region = regionButton.dataset.regionOption;
        regionButtons.forEach((other) => other.setAttribute('aria-pressed', String(other === regionButton)));
        variantButtons.forEach((button) => { button.hidden = button.dataset.variantRegion !== region; });
        selectVariant(variantButtons.find((button) => !button.hidden && !button.disabled) ?? null);
    }));

    variantButtons.forEach((button) => button.addEventListener('click', () => selectVariant(button)));

    form.querySelectorAll('[data-stepper]').forEach((stepper) => {
        const input = stepper.querySelector('input');
        stepper.querySelectorAll('[data-step]').forEach((step) => step.addEventListener('click', () => {
            const next = Number(input.value || 1) + Number(step.dataset.step);
            input.value = String(Math.min(Number(input.max || 10), Math.max(1, next)));
        }));
    });

    form.querySelectorAll('button[type="submit"]').forEach((button) => button.addEventListener('click', () => { intent = button.value; }));
    document.querySelector('[data-sticky-buy]')?.addEventListener('click', () => {
        intent = 'buy';
        form.requestSubmit();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        const values = formValues(form);
        const deliveryValues = Object.fromEntries(Object.entries(values.delivery_values ?? {}).filter(([, value]) => String(value).trim() !== ''));
        const body = { variant_id: values.variant_id, quantity: Number(values.quantity || 1) };

        if (Object.keys(deliveryValues).length > 0) {
            body.delivery_values = deliveryValues;
        }

        const button = intent === 'buy' ? form.querySelector('[data-buy-button]') : form.querySelector('[data-add-button]');
        const result = await busy(button, () => post(`${window.location.origin}/api/v1/storefront/cart/items`, body));

        if (!result.ok) {
            showErrors(form, result);

            return;
        }

        const count = (result.body?.data?.cart?.items ?? []).reduce((sum, item) => sum + Number(item.quantity), 0);
        document.querySelectorAll('[data-bag-count]').forEach((badge) => {
            badge.textContent = String(count);
            badge.classList.toggle('hidden', count === 0);
        });

        if (intent === 'buy') {
            window.location.assign(config().routes.checkoutPage);

            return;
        }

        toast(t('added_to_bag'));
    });
}
