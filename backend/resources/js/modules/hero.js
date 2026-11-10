/**
 * Home hero carousel (owner decision 8, WCAG 2.2.2): advances every `data-interval` ms; a
 * visible pause/play button and page indicators; no auto-advance with prefers-reduced-motion;
 * pauses on hover, focus, touch and swipe and does not resume while the shopper interacts;
 * stops while the carousel is off-screen or the tab is hidden.
 */
export default function hero(root) {
    const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));
    const toggle = root.querySelector('[data-hero-toggle]');

    if (slides.length < 2) {
        return;
    }

    const interval = Number(root.dataset.interval) || 5000;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let index = 0;
    let timer = null;
    let userPaused = reducedMotion.matches;
    let interacting = false;
    let visible = true;

    const show = (next) => {
        index = (next + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            slide.hidden = position !== index;
            slide.querySelector('h1')?.toggleAttribute('aria-hidden', position !== index);
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-current', String(position === index)));
    };

    const running = () => !userPaused && !interacting && visible && !document.hidden;

    const schedule = () => {
        window.clearInterval(timer);
        timer = running() ? window.setInterval(() => show(index + 1), interval) : null;
    };

    const renderToggle = () => {
        const paused = userPaused;
        toggle.setAttribute('aria-label', paused ? toggle.dataset.labelPlay : toggle.dataset.labelPause);
        toggle.querySelector('[data-icon-pause]').classList.toggle('hidden', paused);
        toggle.querySelector('[data-icon-play]').classList.toggle('hidden', !paused);
    };

    toggle.addEventListener('click', () => {
        userPaused = !userPaused;
        interacting = false;
        renderToggle();
        schedule();
    });

    dots.forEach((dot) => dot.addEventListener('click', () => {
        show(Number(dot.dataset.heroDot));
        interacting = true;
        schedule();
    }));

    const track = root.querySelector('[data-hero-track]');
    track.addEventListener('mouseenter', () => { interacting = true; schedule(); });
    track.addEventListener('mouseleave', () => { interacting = false; schedule(); });
    root.addEventListener('focusin', () => { interacting = true; schedule(); });
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) {
            interacting = false;
            schedule();
        }
    });

    let startX = null;
    track.addEventListener('touchstart', (event) => {
        interacting = true;
        startX = event.touches[0].clientX;
        schedule();
    }, { passive: true });
    track.addEventListener('touchend', (event) => {
        if (startX === null) {
            return;
        }

        const delta = event.changedTouches[0].clientX - startX;
        const rtl = document.documentElement.dir === 'rtl';

        if (Math.abs(delta) > 40) {
            show(index + ((delta < 0) !== rtl ? 1 : -1));
        }

        startX = null;
    });

    reducedMotion.addEventListener?.('change', () => {
        userPaused = userPaused || reducedMotion.matches;
        renderToggle();
        schedule();
    });

    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        schedule();
    }).observe(root);

    document.addEventListener('visibilitychange', schedule);

    renderToggle();
    schedule();
}
