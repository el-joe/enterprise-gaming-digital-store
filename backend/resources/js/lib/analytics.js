import { config } from './config.js';

/**
 * Browser analytics (M18, SEO_STRATEGY.md section 12). Only while the page configuration has an
 * `analytics` entry (a GTM container or GA4 measurement id is set) and only after the shopper
 * accepted analytics cookies: until then nothing is loaded and events wait in memory. The tag
 * script carries the page's CSP nonce, so GTM can pass it on. Ecommerce events go to the
 * dataLayer (GTM) or to gtag (GA4 only). The purchase and refund events are server-only (Measurement
 * Protocol) and are refused here: the allow-list below is the only way an event leaves the page.
 */
const ALLOWED = ['view_item_list', 'select_item', 'view_item', 'add_to_cart', 'remove_from_cart', 'begin_checkout', 'add_payment_info'];
const COOKIE = 'bc_consent';
const TAG_HOST = 'https://www.googletagmanager.com';

let queue = [];
let loaded = false;

export function settings() {
    return config().analytics ?? null;
}

export function consent() {
    const match = document.cookie.match(new RegExp(`(?:^|; )${COOKIE}=(granted|denied)`));

    return match ? match[1] : null;
}

export function saveConsent(choice) {
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${COOKIE}=${choice}; Max-Age=${60 * 60 * 24 * 180}; Path=/; SameSite=Lax${secure}`;
}

function nonce() {
    return document.getElementById('store-config')?.nonce ?? '';
}

function gtag() {
    window.dataLayer.push(arguments); // eslint-disable-line prefer-rest-params
}

function send(event, ecommerce) {
    const tag = settings();

    if (tag.gtm) {
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push({ event, ecommerce });
    } else {
        gtag('event', event, ecommerce);
    }
}

/**
 * Track one browser ecommerce event. Silently ignored when analytics is off, browser events are
 * off, or the event is not on the allow-list (never the purchase event).
 */
export function track(event, ecommerce = {}) {
    const tag = settings();

    if (!tag || !tag.events || !ALLOWED.includes(event)) {
        return;
    }

    if (loaded) {
        send(event, ecommerce);
    } else {
        queue.push([event, ecommerce]);
    }
}

/** Load the tag once, after consent and when the browser is idle. */
export function load() {
    const tag = settings();

    if (!tag || loaded || consent() !== 'granted') {
        return;
    }

    loaded = true;
    window.dataLayer = window.dataLayer || [];

    const start = () => {
        const script = document.createElement('script');
        script.async = true;
        script.nonce = nonce();

        if (tag.gtm) {
            window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
            script.src = `${TAG_HOST}/gtm.js?id=${encodeURIComponent(tag.gtm)}`;
        } else {
            window.gtag = gtag;
            gtag('consent', 'default', { analytics_storage: 'granted', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
            gtag('js', new Date());
            gtag('config', tag.ga4);
            script.src = `${TAG_HOST}/gtag/js?id=${encodeURIComponent(tag.ga4)}`;
        }

        document.head.appendChild(script);
        queue.forEach(([event, ecommerce]) => send(event, ecommerce));
        queue = [];
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(start, { timeout: 3000 });
    } else {
        window.setTimeout(start, 1500);
    }
}

/** Events the server rendered for this page (nonced JSON block `#analytics-events`). */
export function trackPageEvents() {
    const element = document.getElementById('analytics-events');

    if (!element) {
        return;
    }

    try {
        JSON.parse(element.textContent).forEach((entry) => track(entry.event, entry.ecommerce));
    } catch {
        // Malformed data is ignored; analytics never breaks the page.
    }
}

/** The GA client id from the `_ga` cookie, sent with place-order so the server event joins the session. */
export function gaClientId() {
    const match = document.cookie.match(/(?:^|; )_ga=GA\d\.\d\.(\d+\.\d+)/);

    return match ? match[1] : null;
}
