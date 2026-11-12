/**
 * Storefront v2 header (Figma "Mega menu" 109:371 and 109:803):
 *
 * - desktop "All categories" panel: opens on click, hovering or focusing a category shows its
 *   top brands; ArrowUp/ArrowDown move between categories, ArrowRight (ArrowLeft in RTL) moves
 *   into the brand panel, Escape closes and returns focus to the button, a click outside closes;
 * - mobile categories sheet (a modal dialog): focus is trapped inside, Escape closes, each
 *   category is an accordion, the search box filters categories and brands.
 */
export default function megaMenu(header) {
    desktop(header);
    sheet(header);
}

function desktop(header) {
    const toggle = header.querySelector('[data-mega-toggle]');
    const panel = header.querySelector('[data-mega-panel]');

    if (!toggle || !panel) {
        return;
    }

    const items = Array.from(panel.querySelectorAll('[data-mega-item]'));
    const sections = Array.from(panel.querySelectorAll('[data-mega-section]'));
    const rtl = document.documentElement.dir === 'rtl';

    const show = (key) => {
        items.forEach((item) => item.toggleAttribute('data-active', item.dataset.megaItem === key));
        sections.forEach((section) => { section.hidden = section.dataset.megaSection !== key; });
    };

    const set = (open, focusFirst = false) => {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));

        if (open) {
            show(items.find((item) => item.hasAttribute('data-active'))?.dataset.megaItem ?? items[0]?.dataset.megaItem);

            if (focusFirst) {
                items[0]?.focus();
            }
        }
    };

    toggle.addEventListener('click', () => set(panel.hidden));
    toggle.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            set(true, true);
        }
    });

    items.forEach((item, index) => {
        item.addEventListener('mouseenter', () => show(item.dataset.megaItem));
        item.addEventListener('focus', () => show(item.dataset.megaItem));
        item.addEventListener('keydown', (event) => {
            const forward = rtl ? 'ArrowLeft' : 'ArrowRight';

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                items[(index + (event.key === 'ArrowDown' ? 1 : items.length - 1)) % items.length].focus();
            } else if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                items[event.key === 'Home' ? 0 : items.length - 1].focus();
            } else if (event.key === forward) {
                event.preventDefault();
                sections.find((section) => !section.hidden)?.querySelector('a')?.focus();
            }
        });
    });

    panel.addEventListener('keydown', (event) => {
        const back = rtl ? 'ArrowRight' : 'ArrowLeft';

        if (event.key === 'Escape') {
            set(false);
            toggle.focus();
        } else if (event.key === back && event.target.closest('[data-mega-section]')) {
            event.preventDefault();
            items.find((item) => item.hasAttribute('data-active'))?.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!panel.hidden && !panel.contains(event.target) && !toggle.contains(event.target)) {
            set(false);
        }
    });
}

function sheet(header) {
    const opener = header.querySelector('[data-sheet-open]');
    const dialog = header.querySelector('[data-sheet]');

    if (!opener || !dialog) {
        return;
    }

    const focusable = () => Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input, select')).filter((element) => element.offsetParent !== null);

    const set = (open) => {
        dialog.hidden = !open;
        opener.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            dialog.querySelector('[data-sheet-close]')?.focus();
        } else {
            opener.focus();
        }
    };

    opener.addEventListener('click', () => set(true));
    dialog.querySelectorAll('[data-sheet-close]').forEach((button) => button.addEventListener('click', () => set(false)));

    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            set(false);

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const elements = focusable();
        const first = elements[0];
        const last = elements[elements.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    dialog.querySelectorAll('[data-accordion-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const region = document.getElementById(button.getAttribute('aria-controls'));
            const open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(open));
            region.hidden = !open;
        });
    });

    const filter = dialog.querySelector('[data-sheet-filter]');

    filter?.addEventListener('input', () => {
        const term = filter.value.trim().toLowerCase();

        dialog.querySelectorAll('[data-sheet-entry]').forEach((entry) => {
            entry.hidden = term !== '' && !entry.dataset.terms.includes(term);
        });
    });
}
