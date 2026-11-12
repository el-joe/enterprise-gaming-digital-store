import { get } from '../lib/http.js';

/**
 * Storefront v2 listing filters (resources/views/storefront/partials/listing.blade.php). The GET
 * form works without JavaScript; here:
 *
 * - the filters go to the URL as comma lists (`?platform=steam,xbox&in_stock=1`), page 1, with
 *   the skeleton grid shown while the next page loads;
 * - on desktop a change applies at once; in the mobile bottom sheet changes only update the
 *   sticky "Apply (N results)" from the facets JSON, and Apply navigates;
 * - accordions (button + region), the brand search, "Show all" and the two-thumb price slider
 *   (whole amounts in the display currency, kept in step with the Min and Max fields);
 * - the sheet is a modal: focus stays inside, Escape and the backdrop close it.
 */
const LISTS = ['category', 'platform', 'region', 'delivery_type'];

export default function filters(root) {
    const form = root.querySelector('[data-filter-form]');
    const panel = root.querySelector('[data-filter-panel]');
    const opener = root.querySelector('[data-filter-open]');
    const backdrop = root.querySelector('[data-filter-backdrop]');
    const applyButton = root.querySelector('[data-apply-count]');
    const desktop = window.matchMedia('(min-width: 1024px)');
    let countRequest = null;

    root.querySelector('[data-nojs-apply]')?.remove();

    const query = () => {
        const data = new FormData(form);
        const params = [];

        LISTS.forEach((key) => {
            const values = data.getAll(`${key}[]`).map(String).filter((value) => value !== '');

            if (values.length > 0) {
                params.push(`${key}=${values.map(encodeURIComponent).join(',')}`);
            }
        });

        ['min_price', 'max_price'].forEach((key) => {
            const value = String(data.get(key) ?? '').trim();

            if (value !== '') {
                params.push(`${key}=${encodeURIComponent(value)}`);
            }
        });

        ['in_stock', 'on_sale'].forEach((key) => {
            if (data.get(key) === '1') {
                params.push(`${key}=1`);
            }
        });

        const sort = String(data.get('sort') ?? '');

        if (sort !== '' && sort !== 'best_selling') {
            params.push(`sort=${encodeURIComponent(sort)}`);
        }

        return params.join('&');
    };

    const navigate = () => {
        const grid = root.querySelector('[data-listing-grid]');
        const skeleton = root.querySelector('[data-skeleton]');

        if (grid && skeleton) {
            grid.replaceChildren(skeleton.content.cloneNode(true));
        }

        const search = query();
        window.location.assign(root.dataset.baseUrl + (search === '' ? '' : `?${search}`));
    };

    const sheetOpen = () => !desktop.matches && panel.hasAttribute('data-open');

    const updateCount = async () => {
        if (!applyButton) {
            return;
        }

        countRequest?.abort();
        countRequest = new AbortController();
        const hide = root.dataset.hide ? `&hide=${root.dataset.hide}` : '';
        const fixed = fixedFilter();

        try {
            const result = await get(`${root.dataset.facetsUrl}?${[query(), fixed, hide.slice(1)].filter(Boolean).join('&')}`, { signal: countRequest.signal });

            if (result.ok) {
                const total = Number(result.body?.data?.total ?? 0);
                applyButton.textContent = total === 0 ? root.dataset.applyNone : total === 1 ? root.dataset.applyOne : root.dataset.applyLabel.replace('__count__', total.toLocaleString());
            }
        } catch {
            // A newer count replaced this one.
        }
    };

    /** The page's own category or brand (from its URL), for the sheet's count. */
    const fixedFilter = () => {
        const match = root.dataset.baseUrl.match(/\/(c|b)\/([a-z0-9-]+)$/);

        return match ? `${match[1] === 'c' ? 'category' : 'platform'}=${match[2]}` : '';
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        navigate();
    });

    root.querySelectorAll('[data-auto-submit]').forEach((control) => {
        control.addEventListener('change', () => {
            if (sheetOpen() && control.closest('[data-filter-panel]')) {
                updateCount();
            } else {
                navigate();
            }
        });
    });

    // Accordions.
    root.querySelectorAll('[data-accordion-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const region = document.getElementById(button.getAttribute('aria-controls'));
            const open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(open));
            region.hidden = !open;
            button.querySelector('[data-accordion-icon]')?.classList.toggle('rotate-180', open);
        });
    });

    // Brand search inside the facet.
    root.querySelectorAll('[data-facet-search]').forEach((input) => {
        const fieldset = input.closest('fieldset');

        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            const more = fieldset.querySelector('[data-facet-more]');

            if (more && term !== '') {
                more.open = true;
            }

            fieldset.querySelectorAll('[data-facet-option]').forEach((option) => {
                option.hidden = term !== '' && !option.dataset.label.includes(term);
            });
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    });

    // Price slider.
    root.querySelectorAll('[data-price-range]').forEach((fieldset) => {
        const slider = fieldset.querySelector('[data-price-slider]');

        if (!slider) {
            return;
        }

        slider.hidden = false;
        const ranges = { min: slider.querySelector('[data-slider="min"]'), max: slider.querySelector('[data-slider="max"]') };
        const inputs = { min: fieldset.querySelector('[data-price-input="min"]'), max: fieldset.querySelector('[data-price-input="max"]') };

        ['min', 'max'].forEach((side) => {
            ranges[side].addEventListener('input', () => {
                if (Number(ranges.min.value) > Number(ranges.max.value)) {
                    ranges[side].value = side === 'min' ? ranges.max.value : ranges.min.value;
                }

                const edge = side === 'min' ? ranges[side].min : ranges[side].max;
                inputs[side].value = ranges[side].value === edge ? '' : ranges[side].value;
            });

            ranges[side].addEventListener('change', () => (sheetOpen() ? updateCount() : navigate()));

            inputs[side].addEventListener('change', () => {
                const value = Number(inputs[side].value);

                if (inputs[side].value !== '' && Number.isFinite(value)) {
                    ranges[side].value = String(Math.round(value));
                }

                if (sheetOpen()) {
                    updateCount();
                }
            });
        });
    });

    // Mobile bottom sheet.
    const focusable = () => Array.from(panel.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]), select, summary')).filter((element) => element.offsetParent !== null);

    const setSheet = (open) => {
        panel.toggleAttribute('data-open', open);
        panel.setAttribute('role', open ? 'dialog' : 'region');
        panel.toggleAttribute('aria-modal', open);
        backdrop.hidden = !open;
        opener?.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            panel.querySelector('[data-filter-close]')?.focus();
        } else {
            opener?.focus();
        }
    };

    opener?.addEventListener('click', () => setSheet(true));
    root.querySelector('[data-filter-close]')?.addEventListener('click', () => setSheet(false));
    backdrop?.addEventListener('click', () => setSheet(false));
    desktop.addEventListener('change', () => {
        if (desktop.matches && panel.hasAttribute('data-open')) {
            setSheet(false);
        }
    });

    panel.addEventListener('keydown', (event) => {
        if (!sheetOpen()) {
            return;
        }

        if (event.key === 'Escape') {
            setSheet(false);

            return;
        }

        if (event.key === 'Tab') {
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
        }
    });
}
