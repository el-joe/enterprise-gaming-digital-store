import { t } from '../lib/config.js';
import { get, post } from '../lib/http.js';

const SCHEDULE = [2000, 3000, 5000];
const LATER_EVERY = 10000;
const GIVE_UP_AFTER = 120000;

/**
 * Payment return page. Asks the server to check with the provider (PayPal: capture the approved
 * order; others: verify), then polls the order status (2, 3, 5 s, then every 10 s for up to two
 * minutes) and opens the order page once it is paid or final. The browser never decides "paid".
 */
export default function paymentReturn(root) {
    const title = root.querySelector('[data-return-title]');
    const text = root.querySelector('[data-return-text]');
    const orderUrl = root.dataset.orderUrl;
    const started = Date.now();
    let attempt = 0;

    const done = (status) => status && (status.payment_status === 'paid' || status.is_final);

    const finish = () => window.location.assign(orderUrl);

    const poll = async () => {
        const result = await get(root.dataset.statusUrl);
        const status = result.ok ? result.body?.data : null;

        if (done(status)) {
            finish();

            return;
        }

        if (Date.now() - started > GIVE_UP_AFTER) {
            title.textContent = t('return_slow_title');
            text.textContent = t('return_slow_text');

            return;
        }

        window.setTimeout(poll, SCHEDULE[attempt++] ?? LATER_EVERY);
    };

    const start = async () => {
        if (root.dataset.paymentStatus === 'paid' || root.dataset.final === '1') {
            finish();

            return;
        }

        const usePaypal = root.dataset.gateway === 'paypal' && root.dataset.cancelled !== '1' && new URLSearchParams(window.location.search).has('token');
        const result = await post(usePaypal ? root.dataset.captureUrl : root.dataset.verifyUrl, {});

        if (result.ok && done(result.body?.data)) {
            finish();

            return;
        }

        window.setTimeout(poll, SCHEDULE[attempt++]);
    };

    start();
}
