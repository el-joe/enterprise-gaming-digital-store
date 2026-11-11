import { config, t } from '../lib/config.js';
import { request } from '../lib/http.js';
import { busy, messageFor, toast } from '../lib/forms.js';

/**
 * Save or remove a product from the wishlist (M13): `POST /api/v1/account/wishlist` and
 * `DELETE /api/v1/account/wishlist/{slug}`. Signed-out shoppers are sent to sign in.
 * `data-remove-card`: on the wishlist page, the card is removed after a successful removal.
 */
export default function wishlist(button) {
    button.addEventListener('click', async () => {
        if (!config().signedIn) {
            window.location.assign(config().routes.login);

            return;
        }

        const saved = button.getAttribute('aria-pressed') === 'true';
        const url = config().routes.wishlist;
        const result = await busy(button, () => (saved
            ? request('DELETE', `${url}/${encodeURIComponent(button.dataset.slug)}`)
            : request('POST', url, { body: { product_slug: button.dataset.slug } })));

        if (!result.ok) {
            toast(messageFor(result));

            return;
        }

        if (saved && button.hasAttribute('data-remove-card')) {
            button.closest('[data-wishlist-card]')?.remove();
            toast(t('wishlist_removed'));

            return;
        }

        button.setAttribute('aria-pressed', saved ? 'false' : 'true');
        button.querySelector('svg')?.classList.toggle('fill-current', !saved);
        button.querySelector('[data-wishlist-label]')?.replaceChildren(document.createTextNode(saved ? t('wishlist_add') : t('wishlist_saved')));
        toast(saved ? t('wishlist_removed') : t('wishlist_added'));
    });
}
