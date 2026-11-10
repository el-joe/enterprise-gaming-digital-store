/** Mobile menu toggle and the "/" shortcut that focuses search (global, once per page). */
export default function menu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const panel = document.querySelector('[data-menu]');

    toggle?.addEventListener('click', () => {
        const open = panel.hidden;
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const typing = target instanceof HTMLElement && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

        if (event.key === '/' && !typing) {
            const input = document.querySelector('[data-search-input]');

            if (input && input.offsetParent !== null) {
                event.preventDefault();
                input.focus();
            }
        }
    });
}
