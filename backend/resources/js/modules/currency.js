import { config } from '../lib/config.js';
import { post } from '../lib/http.js';
import { toast, messageFor } from '../lib/forms.js';

/**
 * Display currency switcher: saves the choice (`POST /api/v1/storefront/currency`, cookie) and
 * reloads, so every price on the page is rendered again by the server in that currency.
 */
export default function currency(root) {
    const select = root.querySelector('[data-currency-select]');

    select?.addEventListener('change', async () => {
        select.disabled = true;
        const result = await post(config().routes.currency, { currency: select.value });

        if (result.ok) {
            window.location.reload();

            return;
        }

        select.disabled = false;
        toast(messageFor(result));
    });
}
