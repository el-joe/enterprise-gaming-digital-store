import { config, t } from '../lib/config.js';
import { get, post } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, toast } from '../lib/forms.js';
import { updateBagCount } from '../lib/bag.js';

/**
 * Product card bag button (Storefront v2): a product with one purchasable variant and no
 * delivery field is added at once through `POST /api/v1/storefront/cart/items`; otherwise the
 * variant chooser (`[data-quick-add-dialog]`, a modal <dialog>: focus stays inside, Esc closes)
 * lists every variant from `/api/v1/storefront/catalog/products/{slug}/quick-add` with its face
 * value, region, display-currency price and availability (unavailable ones disabled), the
 * quantity and the delivery fields. Field errors from the server show beside their fields; the
 * header bag count follows the cart in each response. No inline script or style (strict CSP).
 */
const CART_URL = () => `${window.location.origin}/api/v1/storefront/cart/items`;

let dialog = null;
let opener = null;

export function once() {
    dialog = document.querySelector('[data-quick-add-dialog]');

    if (!dialog) {
        return;
    }

    dialog.querySelectorAll('[data-qa-close]').forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('close', () => opener?.focus());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.querySelector('[data-quick-add-form]').addEventListener('submit', submit);
}

export default function quickAdd(button) {
    button.addEventListener('click', async (event) => {
        event.preventDefault();
        event.stopPropagation();
        opener = button;

        if (button.dataset.variant) {
            const result = await busy(button, () => post(CART_URL(), { variant_id: button.dataset.variant, quantity: 1 }));

            if (result.ok) {
                updateBagCount(result.body?.data?.cart);
                toast(t('added_to_bag'));

                return;
            }

            if (result.status !== 422) {
                toast(result.body?.message ?? t('generic_error'));

                return;
            }
        }

        open(button.dataset.slug, button.dataset.name);
    });
}

async function open(slug, name) {
    if (!dialog) {
        return;
    }

    const form = dialog.querySelector('[data-quick-add-form]');
    clearErrors(form);
    dialog.querySelector('[data-qa-name]').textContent = name ?? '';
    dialog.querySelector('[data-qa-image]').replaceChildren();
    dialog.querySelector('[data-qa-variants]').replaceChildren();
    dialog.querySelector('[data-qa-fields]').replaceChildren();
    dialog.querySelector('[data-qa-loading]').hidden = false;
    dialog.querySelector('[data-qa-variants-wrap]').hidden = true;
    dialog.querySelector('[data-qa-fields]').hidden = true;
    dialog.querySelector('[data-qa-quantity-wrap]').hidden = true;
    dialog.querySelector('[data-qa-submit]').disabled = true;
    dialog.querySelector('[data-qa-product-link]').href = productUrl(slug);
    dialog.showModal();

    const result = await get(`${window.location.origin}/api/v1/storefront/catalog/products/${encodeURIComponent(slug)}/quick-add`);

    if (!result.ok) {
        dialog.querySelector('[data-qa-loading]').hidden = true;
        showErrors(form, result);

        return;
    }

    render(result.body.data);
}

function productUrl(slug) {
    const template = config().routes?.product ?? '/products/__slug__';

    return template.replace('__slug__', encodeURIComponent(slug));
}

function render(data) {
    const product = data.product ?? {};
    dialog.querySelector('[data-qa-name]').textContent = product.name ?? '';
    dialog.querySelector('[data-qa-loading]').hidden = true;

    const image = picture(product.image ?? product.platform?.logo ?? null, product.name ?? '', product.image ? 'object-cover' : 'object-contain p-1.5');

    if (image) {
        dialog.querySelector('[data-qa-image]').append(image);
    }

    const list = dialog.querySelector('[data-qa-variants]');
    let firstEnabled = null;

    (data.variants ?? []).forEach((variant) => {
        const label = document.createElement('label');
        label.className = 'flex min-h-12 cursor-pointer items-center gap-3 rounded-md border border-line-default px-3 py-2 text-sm has-checked:border-accent-primary has-checked:bg-accent-tint has-disabled:cursor-not-allowed has-disabled:opacity-50';

        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'variant_id';
        input.value = variant.public_id;
        input.className = 'size-4 shrink-0 accent-(--color-accent-primary)';
        input.disabled = !variant.purchasable;

        if (!input.disabled && firstEnabled === null) {
            firstEnabled = input;
        }

        const text = document.createElement('span');
        text.className = 'flex min-w-0 flex-1 flex-col';

        const title = document.createElement('span');
        title.className = 'font-semibold';
        title.dir = 'ltr';
        title.textContent = variant.face_value ?? variant.title ?? '';

        const meta = document.createElement('span');
        meta.className = 'text-xs text-content-muted';
        meta.textContent = [variant.region_name ?? variant.region, availability(variant)].filter(Boolean).join(' · ');

        text.append(title, meta);

        const price = document.createElement('span');
        price.className = 'shrink-0 font-display font-bold';
        price.dir = 'ltr';
        price.textContent = variant.price?.formatted ?? '—';

        label.append(input, text, price);
        list.append(label);
    });

    if (firstEnabled) {
        firstEnabled.checked = true;
    }

    dialog.querySelector('[data-qa-variants-wrap]').hidden = false;

    const fields = dialog.querySelector('[data-qa-fields]');
    (data.delivery_fields ?? []).forEach((field) => fields.append(deliveryField(field)));
    fields.hidden = (data.delivery_fields ?? []).length === 0;

    const quantity = dialog.querySelector('[name="quantity"]');
    quantity.value = '1';
    quantity.max = String(product.max_quantity ?? 10);
    dialog.querySelector('[data-qa-quantity-wrap]').hidden = false;
    dialog.querySelector('[data-qa-submit]').disabled = firstEnabled === null;
    (firstEnabled ?? dialog.querySelector('[data-qa-close]')).focus();
}

function availability(variant) {
    if (!variant.purchasable) {
        return dialog.dataset.labelUnavailable;
    }

    return {
        in_stock: dialog.dataset.labelInStock,
        limited: dialog.dataset.labelLimited,
        out_of_stock: dialog.dataset.labelOutOfStock,
    }[variant.availability] ?? '';
}

function deliveryField(field) {
    const id = `qa-field-${field.key}`;
    const wrap = document.createElement('div');

    const label = document.createElement('label');
    label.className = 'field-label';
    label.htmlFor = id;
    label.textContent = field.label ?? field.key;

    let control;

    if (field.type === 'select' && Array.isArray(field.options)) {
        control = document.createElement('select');
        const empty = document.createElement('option');
        empty.value = '';
        control.append(empty);
        field.options.forEach((option) => {
            const element = document.createElement('option');
            element.value = typeof option === 'object' ? option.value : option;
            element.textContent = typeof option === 'object' ? (option.label ?? option.value) : option;
            control.append(element);
        });
    } else {
        control = document.createElement('input');
        control.type = { number: 'text', email: 'email' }[field.type] ?? 'text';

        if (field.type === 'number') {
            control.inputMode = 'numeric';
        }

        if (field.max_length) {
            control.maxLength = field.max_length;
        }
    }

    control.id = id;
    control.name = `delivery_values[${field.key}]`;
    control.className = 'field-input';
    control.required = Boolean(field.required);
    control.dir = 'auto';

    const error = document.createElement('p');
    error.className = 'field-error';
    error.hidden = true;
    error.dataset.errorFor = `delivery_values.${field.key}`;

    wrap.append(label, control);

    if (field.help_text) {
        const help = document.createElement('p');
        help.className = 'field-help';
        help.textContent = field.help_text;
        wrap.append(help);
    }

    wrap.append(error);

    return wrap;
}

function picture(image, alt, fit) {
    if (!image) {
        return null;
    }

    const element = document.createElement('picture');

    ['avif', 'webp'].forEach((format) => {
        const src = image.formats?.[`${format}_480`] ?? image.formats?.[format];

        if (src) {
            const source = document.createElement('source');
            source.type = `image/${format}`;
            source.srcset = src;
            element.append(source);
        }
    });

    const img = document.createElement('img');
    img.src = image.formats?.webp ?? image.url;
    img.alt = alt;
    img.className = `size-full ${fit}`;
    img.decoding = 'async';
    element.append(img);

    return element;
}

async function submit(event) {
    event.preventDefault();

    const form = event.currentTarget;
    clearErrors(form);

    const values = formValues(form);
    const deliveryValues = Object.fromEntries(Object.entries(values.delivery_values ?? {}).filter(([, value]) => String(value).trim() !== ''));
    const body = { variant_id: values.variant_id, quantity: Number(values.quantity || 1) };

    if (Object.keys(deliveryValues).length > 0) {
        body.delivery_values = deliveryValues;
    }

    const result = await busy(form.querySelector('[data-qa-submit]'), () => post(CART_URL(), body));

    if (!result.ok) {
        showErrors(form, result);

        return;
    }

    updateBagCount(result.body?.data?.cart);
    dialog.close();
    toast(t('added_to_bag'));
}
