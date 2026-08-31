/**
 * Google Analytics (GA4) config. Loaded only after cookie consent — the
 * <script> tag that pulls this file in (see website/header.php) is marked
 * type="text/plain" data-cookieconsent="analytics", so it stays inert until
 * assets/js/cookie-consent.js activates it. Never load this any other way.
 */
window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }
gtag('js', new Date());
gtag('config', 'G-0L79QEV5GT');
