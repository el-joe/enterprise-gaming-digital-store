import { request, post } from '../lib/http.js';
import { busy, clearErrors, formValues, showErrors, toast } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * Admin homepage sections (Storefront v2, Figma 116:11200 and 116:11898).
 *
 * - Reorder: drag a row by its handle (pointer events, mouse, pen and touch; no library) or use
 *   the up and down buttons (keyboard); the new order is saved with
 *   `POST /admin/api/homepage-sections/reorder` and announced.
 * - Drawer: add or edit a section (type, English and Arabic title, rule, period, category or
 *   brand, item limit, visibility). "See all" is shown read-only, computed like the server
 *   (HomeSeeAllResolver). Saved as JSON (CSRF) to the admin API; 422 errors show inline.
 * - The drawer is a modal: focus stays inside, Escape closes, focus returns to the opener.
 */
export default function homepageSections(root) {
    const list = root.querySelector('[data-section-list]');
    const api = root.dataset.api;
    const canManage = root.dataset.canManage === '1';

    reorder(root, list, api, canManage);
    drawer(root, list, api, canManage);
}

function rows(list) {
    return Array.from(list.querySelectorAll('[data-section-row]'));
}

function renumber(list) {
    rows(list).forEach((row, index, all) => {
        row.querySelector('[data-position]').textContent = String(index + 1);
        const up = row.querySelector('[data-move="up"]');
        const down = row.querySelector('[data-move="down"]');

        if (up) {
            up.disabled = index === 0;
        }

        if (down) {
            down.disabled = index === all.length - 1;
        }
    });
}

function reorder(root, list, api, canManage) {
    renumber(list);

    if (!canManage) {
        return;
    }

    const save = async () => {
        const ids = rows(list).map((row) => Number(row.dataset.id));
        const result = await post(`${api}/reorder`, { ids });
        toast(result.ok ? root.dataset.saved : (result.body?.message ?? 'Error'));
    };

    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-move]');

        if (!button) {
            return;
        }

        const row = button.closest('[data-section-row]');

        if (button.dataset.move === 'up' && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
        } else if (button.dataset.move === 'down' && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
        } else {
            return;
        }

        renumber(list);
        (button.disabled ? row.querySelector(`[data-move="${button.dataset.move === 'up' ? 'down' : 'up'}"]`) : button).focus();
        save();
    });

    let dragged = null;
    let pointerId = null;
    let moved = false;

    list.addEventListener('pointerdown', (event) => {
        const handle = event.target.closest('[data-drag-handle]');

        if (!handle || event.button !== 0) {
            return;
        }

        dragged = handle.closest('[data-section-row]');
        pointerId = event.pointerId;
        moved = false;
        handle.setPointerCapture(pointerId);
        dragged.toggleAttribute('data-dragging', true);
        event.preventDefault();
    });

    list.addEventListener('pointermove', (event) => {
        if (!dragged || event.pointerId !== pointerId) {
            return;
        }

        const over = rows(list).find((row) => {
            const box = row.getBoundingClientRect();

            return row !== dragged && event.clientY >= box.top && event.clientY <= box.bottom;
        });

        if (!over) {
            return;
        }

        const box = over.getBoundingClientRect();
        list.insertBefore(dragged, event.clientY < box.top + box.height / 2 ? over : over.nextElementSibling);
        moved = true;
        renumber(list);
    });

    const end = (event) => {
        if (!dragged || event.pointerId !== pointerId) {
            return;
        }

        dragged.removeAttribute('data-dragging');
        dragged = null;
        pointerId = null;

        if (moved) {
            save();
        }
    };

    list.addEventListener('pointerup', end);
    list.addEventListener('pointercancel', end);
}

function seeAll(type, rule, category, brand) {
    if (type === 'category_tiles' || type === 'brand_logos') {
        return '/products';
    }

    if (type !== 'product_rail') {
        return '—';
    }

    return {
        best_sellers: '/products?sort=best_selling',
        newest: '/products?sort=newest',
        on_sale: '/products?on_sale=1',
        category: category ? `/c/${category}` : '/products',
        brand: brand ? `/b/${brand}` : '/products',
    }[rule] ?? '/products';
}

function drawer(root, list, api, canManage) {
    const panel = root.querySelector('[data-drawer]');
    const backdrop = root.querySelector('[data-drawer-backdrop]');
    const form = panel.querySelector('[data-drawer-form]');
    let current = null;
    let opener = null;

    const value = (name) => form.querySelector(`[name="${name}"]:checked`)?.value ?? form.querySelector(`[name="${name}"]`)?.value ?? '';

    const sync = () => {
        const type = value('settings[type]');
        const rule = value('settings[rule]');

        form.querySelectorAll('[data-when-type]').forEach((element) => {
            element.hidden = !element.dataset.whenType.split(' ').includes(type);
        });
        form.querySelectorAll('[data-when-rule]').forEach((element) => {
            element.hidden = type !== 'product_rail' || element.dataset.whenRule !== rule;
        });
        form.querySelector('[data-see-all]').textContent = seeAll(type, rule, value('settings[category]'), value('settings[platform]'));
    };

    const set = (name, val) => {
        form.querySelectorAll(`[name="${name}"]`).forEach((input) => {
            if (input.type === 'radio') {
                input.checked = input.value === val;
            } else if (input.type === 'checkbox') {
                input.checked = Boolean(val);
            } else {
                input.value = val ?? '';
            }
        });
    };

    const open = (section, trigger) => {
        current = section;
        opener = trigger;
        clearErrors(form);
        panel.querySelector('[data-drawer-title]').textContent = section ? panel.dataset.titleEdit : panel.dataset.titleNew;
        panel.querySelector('[data-drawer-meta]').textContent = section ? panel.dataset.meta.replace('__position__', String(rows(list).findIndex((row) => Number(row.dataset.id) === section.id) + 1)).replace('__time__', section.updated ?? '') : '';
        panel.querySelector('[data-drawer-delete]').hidden = !section || !canManage;

        set('settings[type]', section?.type ?? 'product_rail');
        set('title', section?.title ?? '');
        set('title_ar', section?.title_ar ?? '');
        set('settings[rule]', section?.rule ?? 'best_sellers');
        set('settings[period]', section?.period ?? 'all');
        set('settings[category]', section?.category ?? '');
        set('settings[platform]', section?.platform ?? '');
        set('settings[limit]', String(section?.limit ?? 12));
        set('is_active', section ? section.is_active : true);
        form.querySelector('[data-drawer-items]').href = section?.edit_url ?? '#';
        form.querySelectorAll('input, select, button[type="submit"]').forEach((control) => { control.disabled = !canManage; });

        sync();
        panel.hidden = false;
        backdrop.hidden = false;
        form.querySelector('[name="settings[type]"]:checked')?.focus();
    };

    const close = () => {
        panel.hidden = true;
        backdrop.hidden = true;
        opener?.focus();
    };

    root.querySelector('[data-section-new]')?.addEventListener('click', (event) => open(null, event.currentTarget));
    document.querySelector('[data-section-new]')?.addEventListener('click', (event) => {
        if (!root.contains(event.currentTarget)) {
            open(null, event.currentTarget);
        }
    });
    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-section-edit]');

        if (button) {
            open(JSON.parse(button.closest('[data-section-row]').dataset.section), button);
        }
    });
    panel.querySelectorAll('[data-drawer-close]').forEach((button) => button.addEventListener('click', close));
    backdrop.addEventListener('click', close);
    form.addEventListener('change', sync);

    panel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();

            return;
        }

        if (event.key === 'Tab') {
            const focusable = Array.from(panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled])')).filter((element) => element.offsetParent !== null || element.type === 'radio');
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    panel.querySelector('[data-drawer-delete]').addEventListener('click', async () => {
        if (!current) {
            return;
        }

        const { confirmed } = await confirmDialog({ title: panel.dataset.deleteConfirm, danger: true });

        if (!confirmed) {
            return;
        }

        const result = await request('DELETE', `${api}/${current.id}`);

        if (result.ok) {
            window.location.reload();
        } else {
            showErrors(form, result);
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        const values = formValues(form);
        const type = values.settings?.type ?? 'product_rail';
        const rule = type === 'product_rail' ? (values.settings?.rule ?? 'best_sellers') : null;
        const settings = { ...(current?.editable ? current.settings : {}), type, limit: Number(values.settings?.limit || 12) };

        delete settings.rule;
        delete settings.period;
        delete settings.category;
        delete settings.platform;

        if (rule) {
            settings.rule = rule;
        }

        if (rule === 'best_sellers' && values.settings?.period && values.settings.period !== 'all') {
            settings.period = values.settings.period;
        }

        if (rule === 'category') {
            settings.category = values.settings?.category ?? '';
        }

        if (rule === 'brand') {
            settings.platform = values.settings?.platform ?? '';
        }

        if (type === 'hero_banners') {
            delete settings.limit;
        }

        const body = {
            title: values.title ?? '',
            title_ar: values.title_ar ?? '',
            source: type === 'hero_banners' || rule === 'manual' ? 'manual' : 'auto',
            is_active: Boolean(values.is_active),
            settings,
        };

        const result = await busy(form.querySelector('[type="submit"]'), () => request(current ? 'PATCH' : 'POST', current ? `${api}/${current.id}` : api, { body }));

        if (!result.ok) {
            showErrors(form, result);

            return;
        }

        window.location.reload();
    });
}
