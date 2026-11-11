import DataTable from 'datatables.net';
import { t } from '../lib/config.js';
import { del, get, post, request } from '../lib/http.js';
import { busy, messageFor, toast } from '../lib/forms.js';
import { confirmDialog } from './dialog.js';

/**
 * The admin table base (M14), on DataTables 3 (no jQuery, no styling package: Tailwind classes
 * in app.css). Server-side processing only: each draw posts paging, the search text, the sort
 * by column position and the filter values to the table's own JSON route
 * (`/admin/api/tables/{key}`), which decides what they mean. Rows arrive with escaped values;
 * HTML cells are built by the server from escaped parts.
 *
 * Around the table: search box (debounced), filters, saved filters (per staff member, server
 * side), column visibility and page length (saved to the server), row selection with bulk
 * actions and "export selected", CSV or XLSX export (queued on the server, then downloaded),
 * numbered pagination and an empty state with "clear filters".
 *
 * Configuration comes from the `data-table-config` JSON attribute written by
 * `<x-admin.table>`.
 */
export default function adminTable(root) {
    const config = JSON.parse(root.dataset.tableConfig);
    const columns = config.columns;
    const state = {
        filters: { ...(config.context ?? {}) },
        hidden: new Set(config.preferences.hidden_columns ?? columns.filter((column) => !column.visible).map((column) => column.key)),
        selected: new Set(),
        exporting: false,
    };
    const el = (name) => root.querySelector(`[data-dt-${name}]`);
    const tableEl = el('table');
    const searchInput = el('search');
    const errorBox = el('error');
    const emptyBox = el('empty');

    const defaultFilter = (config.preferences.saved_filters ?? []).find((filter) => filter.is_default);
    let initialOrder = [[config.order[0] + 1, config.order[1]]];

    if (defaultFilter) {
        applyPayloadToControls(defaultFilter.payload);
        initialOrder = orderFromPayload(defaultFilter.payload) ?? initialOrder;
    }

    const table = new DataTable(tableEl, {
        serverSide: true,
        processing: true,
        searching: true,
        ordering: true,
        paging: true,
        autoWidth: false,
        layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
        pageLength: config.preferences.page_length,
        order: initialOrder,
        search: { search: searchInput?.value ?? '' },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'w-10',
                render: (data, type, row) => `<input type="checkbox" class="size-4 accent-(--color-accent-text)" data-dt-row-check value="${escapeAttribute(row.DT_RowId)}" aria-label="${escapeAttribute(t('select_row'))}">`,
            },
            ...columns.map((column) => ({
                data: column.key,
                name: column.key,
                orderable: column.orderable,
                searchable: false,
                visible: !state.hidden.has(column.key),
                className: column.numeric ? 'dt-type-numeric' : '',
                defaultContent: '',
            })),
        ],
        language: { emptyTable: t('table_empty'), zeroRecords: t('table_no_match'), processing: t('loading') },
        ajax: (data, callback) => {
            const body = {
                draw: data.draw,
                start: data.start,
                length: data.length,
                search: { value: data.search?.value ?? '' },
                order: (data.order ?? []).map((item) => ({ column: item.column - 1, dir: item.dir })).filter((item) => item.column >= 0),
                filters: currentFilters(),
            };

            errorBox.hidden = true;

            post(config.urls.data, body).then((result) => {
                if (!result.ok || !result.body || result.body.error) {
                    errorBox.querySelector('[data-dt-error-text]').textContent = result.body?.error ? t('table_error') : messageFor(result);
                    errorBox.hidden = false;
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });

                    return;
                }

                callback(result.body);
            });
        },
    });

    // Processing: a skeleton over the body while a draw is in flight.
    table.on('processing', (event, settings, processing) => {
        root.setAttribute('aria-busy', String(processing));
        el('skeleton')?.toggleAttribute('hidden', !processing);
    });

    table.on('draw', () => {
        state.selected.clear();
        syncSelection();
        renderPager();
        renderInfo();

        const info = table.page.info();
        emptyBox.hidden = !(info.recordsDisplay === 0 && hasActiveFilters());
    });

    // Search (debounced) and filters.
    let searchTimer = null;
    searchInput?.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => table.search(searchInput.value.trim()).draw(), 350);
    });

    root.querySelectorAll('[data-dt-filter]').forEach((control) => {
        control.addEventListener('change', () => table.draw());
    });

    root.querySelectorAll('[data-dt-clear]').forEach((button) => {
        button.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
            }

            root.querySelectorAll('[data-dt-filter]').forEach((control) => {
                control.value = '';
            });
            table.search('').draw();
        });
    });

    // Page length.
    const lengthSelect = el('length');
    lengthSelect?.addEventListener('change', () => {
        table.page.len(Number(lengthSelect.value)).draw();
        saveState();
    });

    // Column visibility.
    root.querySelectorAll('[data-dt-column-toggle]').forEach((checkbox) => {
        checkbox.checked = !state.hidden.has(checkbox.value);
        checkbox.addEventListener('change', () => {
            const index = columns.findIndex((column) => column.key === checkbox.value);

            if (checkbox.checked) {
                state.hidden.delete(checkbox.value);
            } else {
                state.hidden.add(checkbox.value);
            }

            table.column(index + 1).visible(checkbox.checked);
            saveState();
        });
    });

    // Menus (columns, saved filters, export): disclosure buttons.
    root.querySelectorAll('[data-dt-menu]').forEach((menu) => {
        const toggle = menu.querySelector('[data-dt-menu-toggle]');
        const panel = menu.querySelector('[data-dt-menu-panel]');
        const set = (open) => {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
        };

        toggle.addEventListener('click', () => set(panel.hidden));
        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                set(false);
            }
        });
        menu.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                set(false);
                toggle.focus();
            }
        });
    });

    // Selection and bulk actions.
    tableEl.addEventListener('change', (event) => {
        const target = event.target;

        if (target.matches('[data-dt-row-check]')) {
            target.checked ? state.selected.add(target.value) : state.selected.delete(target.value);
            syncSelection();
        }

        if (target.matches('[data-dt-check-all]')) {
            tableEl.querySelectorAll('[data-dt-row-check]').forEach((checkbox) => {
                checkbox.checked = target.checked;
                target.checked ? state.selected.add(checkbox.value) : state.selected.delete(checkbox.value);
            });
            syncSelection();
        }
    });

    root.querySelectorAll('[data-dt-bulk-action]').forEach((button) => {
        const action = config.bulk.find((item) => item.key === button.dataset.dtBulkAction);

        button.addEventListener('click', async () => {
            const needsNote = button.dataset.needsNote !== undefined;
            const { confirmed, reason } = await confirmDialog({
                title: action.confirm,
                text: t('selected_count', { count: state.selected.size }),
                danger: action.danger,
                reason: needsNote ? { label: t('note'), min: 5 } : null,
            });

            if (!confirmed) {
                return;
            }

            const result = await busy(button, () => post(action.url, { ids: [...state.selected], ...(needsNote ? { note: reason } : {}) }));
            toast(result.ok ? (result.body?.message ?? '') : messageFor(result));

            if (result.ok) {
                table.draw(false);
            }
        });
    });

    // Saved filters.
    const savedList = el('saved-list');
    renderSaved(config.preferences.saved_filters ?? []);

    el('saved-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const input = form.querySelector('input[name="name"]');
        const error = form.querySelector('[data-error-for="name"]');
        error.hidden = true;

        const result = await busy(form.querySelector('[type="submit"]'), () => post(config.urls.saved, {
            name: input.value.trim(),
            is_default: form.querySelector('input[name="is_default"]')?.checked ?? false,
            payload: currentView(),
        }));

        if (!result.ok) {
            error.textContent = result.body?.errors?.name?.[0] ?? Object.values(result.body?.errors ?? {}).flat()[0] ?? messageFor(result);
            error.hidden = false;

            return;
        }

        input.value = '';
        const saved = (config.preferences.saved_filters ?? []).filter((item) => item.name !== result.body.data.name && !(result.body.data.is_default && item.is_default));
        config.preferences.saved_filters = [...saved, result.body.data].sort((a, b) => a.name.localeCompare(b.name));
        renderSaved(config.preferences.saved_filters);
        toast(result.body.message ?? '');
    });

    // Export (queued on the server; the file downloads when ready).
    root.querySelectorAll('[data-dt-export-format]').forEach((button) => {
        button.addEventListener('click', () => startExport(button.dataset.dtExportFormat, button.dataset.dtExportSelected !== undefined ? [...state.selected] : []));
    });

    function currentFilters() {
        const filters = { ...(config.context ?? {}) };

        root.querySelectorAll('[data-dt-filter]').forEach((control) => {
            if (control.value !== '') {
                filters[control.dataset.dtFilter] = control.value;
            }
        });

        return filters;
    }

    function hasActiveFilters() {
        return (searchInput?.value ?? '') !== '' || Array.from(root.querySelectorAll('[data-dt-filter]')).some((control) => control.value !== '');
    }

    function currentView() {
        return {
            search: searchInput?.value.trim() ?? '',
            filters: Object.fromEntries(Object.entries(currentFilters()).filter(([key]) => !(key in (config.context ?? {})))),
            order: table.order().map(([column, dir]) => ({ column: column - 1, dir })).filter((item) => item.column >= 0),
            hidden_columns: [...state.hidden],
        };
    }

    function applyPayloadToControls(payload) {
        if (searchInput) {
            searchInput.value = payload.search ?? '';
        }

        root.querySelectorAll('[data-dt-filter]').forEach((control) => {
            control.value = payload.filters?.[control.dataset.dtFilter] ?? '';
        });

        if (Array.isArray(payload.hidden_columns)) {
            state.hidden = new Set(payload.hidden_columns);
        }
    }

    function orderFromPayload(payload) {
        const order = (payload.order ?? []).map((item) => [item.column + 1, item.dir]);

        return order.length > 0 ? order : null;
    }

    function applySaved(filter) {
        applyPayloadToControls(filter.payload);
        columns.forEach((column, index) => table.column(index + 1).visible(!state.hidden.has(column.key)));
        root.querySelectorAll('[data-dt-column-toggle]').forEach((checkbox) => {
            checkbox.checked = !state.hidden.has(checkbox.value);
        });

        const order = orderFromPayload(filter.payload);

        if (order) {
            table.order(order);
        }

        el('saved-label').textContent = filter.name;
        table.search(searchInput?.value ?? '').draw();
    }

    function renderSaved(filters) {
        if (!savedList) {
            return;
        }

        savedList.replaceChildren(...filters.map((filter) => {
            const item = document.createElement('li');
            item.className = 'flex items-center justify-between gap-2';

            const apply = document.createElement('button');
            apply.type = 'button';
            apply.className = 'flex-1 rounded-sm px-2 py-1.5 text-start text-sm hover:bg-surface-sunken';
            apply.textContent = filter.is_default ? `${filter.name} · ${t('default')}` : filter.name;
            apply.addEventListener('click', () => applySaved(filter));

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'rounded-sm px-2 py-1 text-xs text-content-muted hover:text-status-danger';
            remove.textContent = '×';
            remove.setAttribute('aria-label', t('delete_saved', { name: filter.name }));
            remove.addEventListener('click', async () => {
                const result = await del(config.urls.saved_delete.replace('__id__', String(filter.id)));

                if (result.ok) {
                    config.preferences.saved_filters = (config.preferences.saved_filters ?? []).filter((item) => item.id !== filter.id);
                    renderSaved(config.preferences.saved_filters);
                } else {
                    toast(messageFor(result));
                }
            });

            item.append(apply, remove);

            return item;
        }));

        el('saved-empty')?.toggleAttribute('hidden', filters.length > 0);
    }

    let stateTimer = null;
    function saveState() {
        window.clearTimeout(stateTimer);
        stateTimer = window.setTimeout(() => {
            request('PUT', config.urls.state, { body: { hidden_columns: [...state.hidden], page_length: table.page.len() } });
        }, 600);
    }

    async function startExport(format, ids) {
        if (state.exporting) {
            return;
        }

        state.exporting = true;
        const status = el('export-status');
        status.textContent = t('export_queued');
        status.hidden = false;

        const result = await post(config.urls.export, { format, ...currentView(), ids });

        if (!result.ok) {
            state.exporting = false;
            status.hidden = true;
            toast(Object.values(result.body?.errors ?? {}).flat()[0] ?? messageFor(result));

            return;
        }

        const id = result.body.data.id;

        for (let attempt = 0; attempt < 90; attempt++) {
            const check = await get(config.urls.export_status.replace('__id__', String(id)));
            const data = check.body?.data;

            if (check.ok && data?.status === 'completed' && data.download_url) {
                status.textContent = t('export_ready', { rows: data.rows ?? 0 });
                window.location.assign(data.download_url);
                break;
            }

            if (!check.ok || data?.status === 'failed') {
                status.textContent = t('export_failed');
                break;
            }

            status.textContent = t(data?.status === 'running' ? 'export_running' : 'export_waiting');
            await new Promise((resolve) => window.setTimeout(resolve, 2000));
        }

        state.exporting = false;
        window.setTimeout(() => {
            status.hidden = true;
        }, 6000);
    }

    function syncSelection() {
        const count = state.selected.size;
        const bar = el('bulk');
        bar.hidden = count === 0;
        el('bulk-count').textContent = t('selected_count', { count });

        tableEl.querySelectorAll('tbody tr').forEach((row) => {
            const checkbox = row.querySelector('[data-dt-row-check]');

            if (checkbox) {
                checkbox.checked = state.selected.has(checkbox.value);
                row.toggleAttribute('data-selected', checkbox.checked);
            }
        });

        const all = tableEl.querySelector('[data-dt-check-all]');
        const boxes = tableEl.querySelectorAll('[data-dt-row-check]');

        if (all) {
            all.checked = boxes.length > 0 && count === boxes.length;
            all.indeterminate = count > 0 && count < boxes.length;
        }
    }

    function renderInfo() {
        const info = table.page.info();
        el('info').textContent = info.recordsDisplay === 0
            ? t('showing_none')
            : t('showing', { from: info.start + 1, end: info.end, total: info.recordsDisplay });
    }

    function renderPager() {
        const pager = el('pager');
        const info = table.page.info();
        const pages = info.pages;
        const current = info.page;
        const buttons = [];

        const make = (label, page, { disabled = false, active = false, aria = null } = {}) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.disabled = disabled;
            button.className = active
                ? 'inline-flex size-9 items-center justify-center rounded-sm bg-accent-primary text-sm font-semibold text-content-on-accent'
                : 'inline-flex size-9 items-center justify-center rounded-sm border border-line-default bg-surface-raised text-sm text-content-primary hover:border-line-strong disabled:opacity-40';

            if (active) {
                button.setAttribute('aria-current', 'page');
            }

            if (aria) {
                button.setAttribute('aria-label', aria);
            }

            if (!disabled && !active) {
                button.addEventListener('click', () => table.page(page).draw('page'));
            }

            return button;
        };

        const gap = () => {
            const span = document.createElement('span');
            span.className = 'inline-flex size-9 items-center justify-center text-sm text-content-muted';
            span.textContent = '…';

            return span;
        };

        if (pages <= 1) {
            pager.replaceChildren();

            return;
        }

        const rtl = document.documentElement.dir === 'rtl';
        buttons.push(make(rtl ? '›' : '‹', current - 1, { disabled: current === 0, aria: t('previous') }));

        const window_ = new Set([0, pages - 1, current - 1, current, current + 1].filter((page) => page >= 0 && page < pages));
        let last = -1;

        [...window_].sort((a, b) => a - b).forEach((page) => {
            if (page - last > 1) {
                buttons.push(gap());
            }

            buttons.push(make(String(page + 1), page, { active: page === current }));
            last = page;
        });

        buttons.push(make(rtl ? '‹' : '›', current + 1, { disabled: current >= pages - 1, aria: t('next') }));
        pager.replaceChildren(...buttons);
    }
}

function escapeAttribute(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
}
