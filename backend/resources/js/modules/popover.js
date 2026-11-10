/** A disclosure popover (language and currency): toggles, closes on Escape and outside click. */
export default function popover(root) {
    const toggle = root.querySelector('[data-popover-toggle]');
    const panel = root.querySelector('[data-popover-panel]');

    const set = (open) => {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => set(panel.hidden));
    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            set(false);
        }
    });
    root.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            set(false);
            toggle.focus();
        }
    });
}
