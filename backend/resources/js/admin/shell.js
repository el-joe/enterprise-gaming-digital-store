import { config } from '../lib/config.js';
import { post } from '../lib/http.js';

const RAIL_KEY = 'admin.rail';

/**
 * The admin shell (M14): the collapsible sidebar rail (remembered in this browser only), the
 * mobile navigation drawer, the user menu and sign-out. Global, once per page.
 */
export default function shell(root) {
    const sidebar = root.querySelector('[data-admin-sidebar]');
    const railToggle = root.querySelector('[data-rail-toggle]');
    const drawerToggle = root.querySelector('[data-drawer-toggle]');
    const scrim = root.querySelector('[data-drawer-scrim]');

    const setRail = (collapsed) => {
        root.classList.toggle('admin-rail', collapsed);
        railToggle?.setAttribute('aria-expanded', String(!collapsed));
    };

    try {
        setRail(window.localStorage.getItem(RAIL_KEY) === '1');
    } catch {
        setRail(false);
    }

    railToggle?.addEventListener('click', () => {
        const collapsed = !root.classList.contains('admin-rail');
        setRail(collapsed);

        try {
            window.localStorage.setItem(RAIL_KEY, collapsed ? '1' : '0');
        } catch {
            // Storage unavailable: the choice lasts for this page only.
        }
    });

    const setDrawer = (open) => {
        sidebar.classList.toggle('max-md:-translate-x-full', !open);
        sidebar.classList.toggle('max-md:rtl:translate-x-full', !open);
        scrim.hidden = !open;
        drawerToggle?.setAttribute('aria-expanded', String(open));

        if (open) {
            sidebar.querySelector('a, button')?.focus();
        }
    };

    drawerToggle?.addEventListener('click', () => setDrawer(scrim.hidden));
    scrim?.addEventListener('click', () => setDrawer(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && scrim && !scrim.hidden) {
            setDrawer(false);
            drawerToggle?.focus();
        }
    });

    root.querySelectorAll('[data-logout]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            await post(config().routes.logout, {});
            window.location.assign(config().routes.login);
        });
    });
}
