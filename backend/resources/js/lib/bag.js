/** The header bag badge, from the cart in a cart JSON response (`data.cart.items`). */
export function updateBagCount(cart) {
    const count = (cart?.items ?? []).reduce((sum, item) => sum + Number(item.quantity), 0);

    document.querySelectorAll('[data-bag-count]').forEach((badge) => {
        badge.textContent = String(count);
        badge.classList.toggle('hidden', count === 0);
    });

    return count;
}
