import { config, t } from '../lib/config.js';
import { get } from '../lib/http.js';

const RECENT_KEY = 'boxcards.recentSearches';
const RECENT_MAX = 5;

/**
 * Instant search (spec 6): suggestions from `GET /api/v1/storefront/search/suggest` grouped by
 * games/products, platforms and categories; popular searches; and recent searches kept only in
 * this browser (localStorage, never sent to the server).
 */
export default function search(form) {
    const input = form.querySelector('[data-search-input]');
    const panel = form.querySelector('[data-search-panel]');
    let timer = null;
    let controller = null;
    let active = -1;
    let popular = null;

    const options = () => Array.from(panel.querySelectorAll('[role="option"]'));

    const close = () => {
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };

    const open = () => {
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const render = (groups) => {
        panel.replaceChildren();
        let index = 0;

        groups.filter((group) => group.items.length > 0).forEach((group) => {
            const heading = document.createElement('p');
            heading.className = 'eyebrow mb-1 mt-3 text-content-muted first:mt-0';
            heading.textContent = group.title;
            panel.appendChild(heading);

            const list = document.createElement('ul');
            list.setAttribute('role', 'listbox');
            list.className = group.chips ? 'flex flex-wrap gap-2' : 'flex flex-col';

            group.items.forEach((item) => {
                const li = document.createElement('li');
                const link = document.createElement('a');
                link.href = item.href;
                link.id = `search-option-${index++}`;
                link.setAttribute('role', 'option');
                link.className = group.chips
                    ? 'chip min-h-8 hover:text-content-primary aria-selected:text-content-primary aria-selected:ring-1 aria-selected:ring-focus-ring'
                    : 'flex min-h-10 items-center justify-between gap-3 rounded-md px-2 text-sm hover:bg-surface-raised aria-selected:bg-surface-raised';
                link.textContent = item.label;

                if (item.image) {
                    // The product image (Storefront v2): product image, else the brand logo.
                    const picture = document.createElement('img');
                    picture.src = item.image.formats?.webp_480 ?? item.image.formats?.webp ?? item.image.url;
                    picture.alt = '';
                    picture.loading = 'lazy';
                    picture.decoding = 'async';
                    picture.className = 'size-8 shrink-0 rounded-xs bg-surface-overlay object-cover';
                    link.prepend(picture);
                    link.classList.add('justify-start');
                }

                if (item.hint) {
                    const hint = document.createElement('span');
                    hint.className = 'text-xs text-content-muted';
                    hint.textContent = item.hint;
                    link.appendChild(hint);
                }

                if (item.term) {
                    link.addEventListener('click', () => remember(item.term));
                }

                li.appendChild(link);
                list.appendChild(li);
            });

            panel.appendChild(list);
        });

        if (panel.childElementCount === 0) {
            close();
        } else {
            open();
        }
    };

    const searchUrl = (term) => `${config().routes.search}?q=${encodeURIComponent(term)}`;

    const idle = async () => {
        if (popular === null) {
            const result = await get(`${config().routes.popular}?locale=${config().locale}`);
            popular = result.ok ? result.body?.data?.terms ?? [] : [];
        }

        render([
            { title: t('recent_searches'), chips: true, items: recent().map((term) => ({ label: term, href: searchUrl(term), term })) },
            { title: t('popular_searches'), chips: true, items: popular.map((term) => ({ label: term, href: searchUrl(term), term })) },
        ]);
    };

    const suggest = async (term) => {
        controller?.abort();
        controller = new AbortController();

        let result;

        try {
            result = await get(`${config().routes.suggest}?q=${encodeURIComponent(term)}&locale=${config().locale}`, { signal: controller.signal });
        } catch {
            return;
        }

        const suggestions = result.ok ? result.body?.data?.suggestions ?? [] : [];
        const routes = config().routes;
        const link = (type, slug) => (routes[type] ?? routes.search).replace('__slug__', encodeURIComponent(slug));
        const of = (type, route, hint) => suggestions.filter((item) => item.type === type).map((item) => ({ label: item.title, href: link(route, item.slug), hint, image: item.image ?? null }));

        render([
            { title: t('suggest_products'), items: of('product', 'product', t('type_product')) },
            { title: t('suggest_platforms'), items: of('gaming_platform', 'platform', t('type_platform')) },
            { title: t('suggest_categories'), items: of('category', 'category', t('type_category')) },
            { title: t('search_all'), items: [{ label: t('search_for', { term }), href: searchUrl(term), term }] },
        ]);
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        const term = input.value.trim();

        timer = window.setTimeout(() => (term.length >= 2 ? suggest(term) : idle()), 200);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length < 2) {
            idle();
        }
    });

    input.addEventListener('keydown', (event) => {
        const list = options();

        if (event.key === 'Escape') {
            close();

            return;
        }

        if (!['ArrowDown', 'ArrowUp'].includes(event.key) || list.length === 0) {
            if (event.key === 'Enter' && active >= 0 && list[active]) {
                event.preventDefault();
                list[active].click();
            }

            return;
        }

        event.preventDefault();
        active = event.key === 'ArrowDown' ? (active + 1) % list.length : (active - 1 + list.length) % list.length;
        list.forEach((option, index) => option.setAttribute('aria-selected', String(index === active)));
        input.setAttribute('aria-activedescendant', list[active].id);
        list[active].scrollIntoView({ block: 'nearest' });
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            close();
        }
    });

    form.addEventListener('submit', (event) => {
        const term = input.value.trim();

        if (term.length < 2) {
            event.preventDefault();
            input.focus();

            return;
        }

        remember(term);
    });

    if (form.hasAttribute('data-search-page')) {
        if (input.value.trim().length >= 2) {
            remember(input.value.trim());
        }

        showRecentOnPage();
    }
}

function recent() {
    try {
        const stored = JSON.parse(window.localStorage.getItem(RECENT_KEY) ?? '[]');

        return Array.isArray(stored) ? stored.filter((term) => typeof term === 'string').slice(0, RECENT_MAX) : [];
    } catch {
        return [];
    }
}

function remember(term) {
    try {
        const terms = [term, ...recent().filter((existing) => existing.toLowerCase() !== term.toLowerCase())].slice(0, RECENT_MAX);
        window.localStorage.setItem(RECENT_KEY, JSON.stringify(terms));
    } catch {
        // Storage can be unavailable (private mode); recent searches are optional.
    }
}

function showRecentOnPage() {
    const block = document.querySelector('[data-recent-searches]');
    const list = block?.querySelector('[data-recent-list]');
    const terms = recent();

    if (!block || terms.length === 0) {
        return;
    }

    list.replaceChildren(...terms.map((term) => {
        const li = document.createElement('li');
        const link = document.createElement('a');
        link.href = `${config().routes.search}?q=${encodeURIComponent(term)}`;
        link.className = 'chip min-h-8 hover:text-content-primary';
        link.textContent = term;
        li.appendChild(link);

        return li;
    }));
    block.hidden = false;
}
