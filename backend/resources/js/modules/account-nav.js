/** Account navigation (M13): below lg the tabs scroll sideways; keep the current one in view. */
export default function accountNav(nav) {
    const current = nav.querySelector('[aria-current="page"]');
    const list = nav.querySelector('ul');

    if (current && list && list.scrollWidth > list.clientWidth) {
        current.scrollIntoView({ block: 'nearest', inline: 'center' });
    }
}
