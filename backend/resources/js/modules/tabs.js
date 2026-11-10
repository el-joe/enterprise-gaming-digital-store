/** WAI-ARIA tabs with arrow-key navigation (direction-aware). */
export default function tabs(root) {
    const list = Array.from(root.querySelectorAll('[role="tab"]'));

    const select = (tab) => {
        list.forEach((other) => {
            const selected = other === tab;
            other.setAttribute('aria-selected', String(selected));
            other.tabIndex = selected ? 0 : -1;
            document.getElementById(other.getAttribute('aria-controls')).hidden = !selected;
        });
        tab.focus();
    };

    list.forEach((tab, index) => {
        tab.addEventListener('click', () => select(tab));
        tab.addEventListener('keydown', (event) => {
            const rtl = document.documentElement.dir === 'rtl';
            const forward = rtl ? 'ArrowLeft' : 'ArrowRight';
            const back = rtl ? 'ArrowRight' : 'ArrowLeft';

            if (event.key === forward) {
                select(list[(index + 1) % list.length]);
            } else if (event.key === back) {
                select(list[(index - 1 + list.length) % list.length]);
            }
        });
    });
}
