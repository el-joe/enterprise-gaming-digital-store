import { del, fragment, patch, post } from '../lib/http.js';
import { busy, clearErrors, showErrors, toast, messageFor } from '../lib/forms.js';
import { t } from '../lib/config.js';

const API = '/api/v1/storefront/cart';

/**
 * Bag actions (quantity, remove, coupon) through the cart JSON, then the server fragment is
 * fetched again so all amounts stay server-rendered. Used on the bag page (`cart` fragment) and
 * the checkout summary (`summary` fragment); dispatches `cart:updated` after each change.
 */
export default function cart(root) {
    const name = root.dataset.fragmentName ?? 'cart';
    const container = root.dataset.fragment ? root : root.querySelector('[data-fragment]');
    const url = root.dataset.fragmentUrl;

    const refresh = async () => {
        const html = await fragment(url, name);

        if (html !== null) {
            container.innerHTML = html;
        }

        document.dispatchEvent(new CustomEvent('cart:updated'));
    };

    const run = async (button, call, errorScope) => {
        clearErrors(errorScope);
        const result = await busy(button, call);

        if (!result.ok) {
            if (result.body?.code === 'coupon_invalid' && !result.body?.errors) {
                result.body.errors = { code: [result.body.message] };
            }

            showErrors(errorScope, result);

            const shown = Array.from(errorScope.querySelectorAll('[data-error-for]')).some((element) => !element.hidden);

            if (!shown && !errorScope.querySelector('[data-form-error]')) {
                const first = Object.values(result.body?.errors ?? {})[0];
                toast(Array.isArray(first) ? first[0] : messageFor(result));
            }

            return false;
        }

        await refresh();

        return true;
    };

    root.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('button') : null;

        if (!target) {
            return;
        }

        const item = target.closest('[data-cart-item]');

        if (target.hasAttribute('data-cart-quantity') && item) {
            run(target, () => patch(`${API}/items/${item.dataset.cartItem}`, { quantity: Number(target.dataset.cartQuantity) }), item);
        } else if (target.hasAttribute('data-cart-remove') && item) {
            run(target, () => del(`${API}/items/${item.dataset.cartItem}`), item).then((done) => done && toast(t('removed_from_bag')));
        } else if (target.hasAttribute('data-coupon-apply')) {
            const scope = target.closest('[data-coupon]');
            const code = scope.querySelector('input[name="code"]').value.trim();
            run(target, () => post(`${API}/coupon`, { code }), scope);
        } else if (target.hasAttribute('data-coupon-remove')) {
            run(target, () => del(`${API}/coupon`), target.closest('[data-coupon]'));
        }
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target instanceof HTMLInputElement && event.target.name === 'code') {
            event.preventDefault();
            event.target.closest('[data-coupon]')?.querySelector('[data-coupon-apply]')?.click();
        }
    });
}
