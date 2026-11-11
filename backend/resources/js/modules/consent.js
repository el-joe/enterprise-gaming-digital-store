import { consent, load, saveConsent, track, trackPageEvents } from '../lib/analytics.js';

/**
 * Analytics consent banner (M18, storefront/partials/consent.blade.php). Shown while no choice is
 * saved; "Accept" loads the tag (lib/analytics.js), "Decline" keeps it off. The footer's
 * "Cookie settings" reopens it. Also receives `store:analytics` events from other modules.
 */
export function once() {
    trackPageEvents();
    document.addEventListener('store:analytics', (event) => track(event.detail?.event, event.detail?.ecommerce ?? {}));
    load();
}

export default function consentBanner(banner) {
    const open = () => {
        banner.hidden = false;
        banner.querySelector('[data-consent-choice]')?.focus();
    };

    if (consent() === null) {
        banner.hidden = false;
    }

    banner.querySelectorAll('[data-consent-choice]').forEach((button) => button.addEventListener('click', () => {
        const choice = button.dataset.consentChoice === 'granted' ? 'granted' : 'denied';
        const wasGranted = consent() === 'granted';
        saveConsent(choice);
        banner.hidden = true;

        if (choice === 'granted') {
            load();
        } else if (wasGranted) {
            // A loaded tag cannot be unloaded: reload so it is gone from now on.
            window.location.reload();
        }
    }));

    document.querySelectorAll('[data-consent-open]').forEach((button) => button.addEventListener('click', open));
}
