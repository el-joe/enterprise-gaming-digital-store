import { config, t } from '../lib/config.js';
import { fragment, get, post } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, showFormError } from '../lib/forms.js';
import { continuePayment } from '../lib/payment.js';

/**
 * Checkout (spec 8, API 3a/3b). Payment options come from `GET /api/v1/checkout/payment-options`
 * for the chosen country; the "What you will be charged" box shows exactly the option's
 * `display_amount` and `payment_amount` / `payment_currency` from that response, and the order is
 * placed with `expected_total` = the option's display amount (what the shopper was shown). The
 * page does no money arithmetic. One Idempotency-Key per request body, reused on retry.
 */
export default function checkout(form) {
    const country = form.querySelector('[data-country]');
    const optionsBox = form.querySelector('[data-options]');
    const chargeBox = form.querySelector('[data-charge-box]');
    const payButton = form.querySelector('[data-pay-button]');
    const payLabel = form.querySelector('[data-pay-label]');
    const notice = form.querySelector('[data-currency-notice]');
    const keys = new Map();
    let options = [];
    let selected = null;
    let loading = null;

    const optionKey = (option, method) => `${option.gateway}|${method?.code ?? ''}`;

    const choose = (option, method) => {
        selected = option ? { option, method } : null;
        payButton.disabled = !option;

        if (!option) {
            chargeBox.hidden = true;
            notice.hidden = true;
            payLabel.textContent = t('pay');

            return;
        }

        const differs = option.payment_currency !== option.display_currency;
        chargeBox.querySelector('[data-charge-display]').textContent = option.display_amount.formatted;
        chargeBox.querySelector('[data-charge-payment]').textContent = option.payment_amount.formatted;
        chargeBox.querySelector('[data-charge-currency]').textContent = option.payment_currency;
        chargeBox.querySelector('[data-charge-note]').textContent = differs
            ? t('charged_in_other', { currency: option.payment_currency, amount: ltr(option.payment_amount.formatted) })
            : t('charged_same', { currency: option.payment_currency });
        chargeBox.hidden = false;

        notice.hidden = !differs;
        notice.querySelector('[data-currency-notice-text]').textContent = t('currency_differs', { display: option.display_currency, payment: option.payment_currency });
        payLabel.textContent = t('pay_amount', { amount: ltr(option.payment_amount.formatted) });
    };

    const renderOptions = () => {
        optionsBox.replaceChildren();

        if (options.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-content-secondary';
            empty.textContent = t('no_payment_options');
            optionsBox.appendChild(empty);
            choose(null);

            return;
        }

        const previous = selected ? optionKey(selected.option, selected.method) : null;
        let restored = null;

        options.forEach((option) => {
            const methods = option.methods.length > 0 ? option.methods : [null];

            methods.forEach((method) => {
                const key = optionKey(option, method);
                const label = document.createElement('label');
                label.className = 'flex cursor-pointer items-center gap-4 rounded-md border border-line-default bg-surface-sunken p-4 has-checked:border-accent-primary has-checked:bg-accent-tint has-disabled:cursor-not-allowed has-disabled:opacity-50';

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'payment_option';
                input.value = key;
                input.className = 'size-5 accent-(--color-accent-primary)';
                input.disabled = !option.available;
                input.addEventListener('change', () => choose(option, method));

                const text = document.createElement('span');
                text.className = 'flex flex-1 flex-col';
                const title = document.createElement('span');
                title.className = 'font-medium';
                title.textContent = method ? `${option.label} · ${method.label}` : option.label;
                const detail = document.createElement('span');
                detail.className = 'text-xs text-content-secondary';
                detail.textContent = option.available
                    ? t('charged_in', { currency: option.payment_currency, amount: ltr(option.payment_amount.formatted) })
                    : t('option_unavailable');
                text.append(title, detail);

                const badge = document.createElement('span');
                badge.className = 'rounded-xs border border-line-strong px-1.5 font-mono text-xs';
                badge.textContent = option.payment_currency;

                label.append(input, text, badge);
                optionsBox.appendChild(label);

                if (option.available && (key === previous || (previous === null && restored === null))) {
                    restored = { input, option, method };
                }
            });
        });

        if (restored) {
            restored.input.checked = true;
            choose(restored.option, restored.method);
        } else {
            choose(null);
        }
    };

    const loadOptions = async () => {
        clearErrors(form);

        if (!country.value) {
            options = [];
            choose(null);

            return;
        }

        optionsBox.setAttribute('aria-busy', 'true');
        const email = form.querySelector('input[name="email"]')?.value.trim();
        const query = new URLSearchParams({ country: country.value, ...(email ? { email } : {}) });
        const request = get(`${form.dataset.optionsUrl}?${query}`);
        loading = request;
        const result = await request;

        if (loading !== request) {
            return;
        }

        optionsBox.setAttribute('aria-busy', 'false');

        if (!result.ok) {
            options = [];
            renderOptions();
            showErrors(form, result);

            return;
        }

        options = result.body?.data?.options ?? [];
        renderOptions();
    };

    const refreshSummary = async () => {
        const summary = document.querySelector('[data-fragment="summary"]');
        const html = await fragment(form.dataset.summaryUrl, 'summary');

        if (summary && html !== null) {
            summary.innerHTML = html;
        }
    };

    country.addEventListener('change', loadOptions);
    form.querySelector('input[name="email"]')?.addEventListener('change', () => country.value && loadOptions());
    document.addEventListener('cart:updated', loadOptions);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        if (!selected) {
            showFormError(form, t('choose_payment'));

            return;
        }

        const values = formValues(form);
        const body = {
            country: country.value,
            gateway: selected.option.gateway,
            currency: selected.option.display_currency,
            expected_total: selected.option.display_amount.amount,
            consents: { terms: values.consents?.terms === true, marketing: values.consents?.marketing === true },
            client: 'web',
        };

        if (selected.method) {
            body.method = selected.method.code;
        }

        if (values.email !== undefined) {
            body.email = values.email.trim();
        }

        const fingerprint = JSON.stringify(body);

        if (!keys.has(fingerprint)) {
            keys.set(fingerprint, `web-${randomKey()}`);
        }

        const result = await busy(payButton, () => post(form.dataset.placeUrl, body, { headers: { 'Idempotency-Key': keys.get(fingerprint) } }));
        const orderId = result.body?.data?.order?.public_id;

        if (result.ok && orderId) {
            const orderUrl = form.dataset.orderUrl.replace('__order__', orderId);

            if (result.body?.meta?.duplicate) {
                window.location.assign(orderUrl);

                return;
            }

            payButton.disabled = true;
            const error = await continuePayment(result.body.data.next ?? null, {
                orderUrl,
                returnUrl: form.dataset.returnUrl.replace('__order__', orderId),
                stripeKey: form.dataset.stripeKey,
                stripePanel: form.querySelector('[data-stripe-panel]'),
            });

            if (error) {
                showFormError(form, error);
            }

            return;
        }

        if (result.status === 401) {
            window.location.assign(config().routes.login);

            return;
        }

        showErrors(form, result);

        if (result.body?.code === 'price_changed') {
            await refreshSummary();
            await loadOptions();
            showFormError(form, result.body.message);
        }
    });

    if (country.value) {
        loadOptions();
    }
}

function randomKey() {
    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);

    return Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
}

/** Keep an amount left to right inside Arabic text (Unicode isolate marks; owner decision 2). */
function ltr(value) {
    return `\u2066${value}\u2069`;
}
