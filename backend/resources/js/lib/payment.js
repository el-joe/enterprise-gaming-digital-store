import { t } from './config.js';

/**
 * Continue a payment from the `next` object of place-order or retry-payment (API 3b). The
 * browser only ever goes to the provider or back to the store; the order becomes paid through a
 * verified webhook or a server-side provider read, never because of anything done here.
 *
 * @returns {Promise<string|null>} an error message to show, or null when the page navigates
 */
export async function continuePayment(next, { orderUrl, returnUrl, stripeKey, stripePanel }) {
    if (!next || next.type === 'wallet') {
        window.location.assign(orderUrl);

        return null;
    }

    if (next.type === 'redirect' && safeUrl(next.redirect_url)) {
        window.location.assign(next.redirect_url);

        return null;
    }

    if (next.type === 'paypal' && safeUrl(next.approve_url)) {
        window.location.assign(next.approve_url);

        return null;
    }

    if (next.type === 'stripe' && next.client_secret) {
        if (!stripeKey || !stripePanel) {
            return t('stripe_unavailable');
        }

        return mountStripe(next.client_secret, stripeKey, stripePanel, returnUrl);
    }

    window.location.assign(orderUrl);

    return null;
}

function safeUrl(value) {
    try {
        const url = new URL(value, window.location.href);

        return url.protocol === 'https:' || url.origin === window.location.origin;
    } catch {
        return false;
    }
}

let stripeScript = null;

function loadStripe() {
    stripeScript ??= new Promise((resolve, reject) => {
        if (window.Stripe) {
            resolve(window.Stripe);

            return;
        }

        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = () => resolve(window.Stripe);
        script.onerror = () => reject(new Error('stripe'));
        document.head.appendChild(script);
    });

    return stripeScript;
}

async function mountStripe(clientSecret, key, panel, returnUrl) {
    let Stripe;

    try {
        Stripe = await loadStripe();
    } catch {
        return t('stripe_unavailable');
    }

    const stripe = Stripe(key);
    const elements = stripe.elements({
        clientSecret,
        locale: document.documentElement.lang,
        appearance: { theme: 'night', variables: { colorPrimary: '#c8ff2e', colorBackground: '#07080b', borderRadius: '10px' } },
    });
    const element = elements.create('payment');
    element.mount(panel.querySelector('[data-stripe-element]'));
    panel.hidden = false;
    panel.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const confirm = panel.querySelector('[data-stripe-confirm]');

    return new Promise((resolve) => {
        confirm.addEventListener('click', async () => {
            confirm.disabled = true;
            const { error } = await stripe.confirmPayment({ elements, confirmParams: { return_url: returnUrl } });
            confirm.disabled = false;

            if (error) {
                resolve(error.message ?? t('generic_error'));
            }
        });
    });
}
