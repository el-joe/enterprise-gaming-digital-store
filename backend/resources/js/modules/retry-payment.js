import { post } from '../lib/http.js';
import { messageFor } from '../lib/forms.js';
import { continuePayment } from '../lib/payment.js';

/** "Try payment again" on an unpaid order: `retry-payment` on the order's gateway, then continue. */
export default function retryPayment(root) {
    const button = root.querySelector('[data-retry]');
    const error = root.querySelector('[data-retry-error]');

    button.addEventListener('click', async () => {
        button.disabled = true;
        error.hidden = true;
        const result = await post(root.dataset.retryUrl, { client: 'web' });

        if (!result.ok) {
            button.disabled = false;
            error.textContent = messageFor(result);
            error.hidden = false;

            return;
        }

        const message = await continuePayment(result.body?.data?.next ?? null, {
            orderUrl: window.location.href,
            returnUrl: root.dataset.returnUrl,
            stripeKey: null,
            stripePanel: null,
        });

        if (message) {
            button.disabled = false;
            error.textContent = message;
            error.hidden = false;
        }
    });
}
