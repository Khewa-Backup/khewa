/**
 * 2007-2026 PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 *  @author    PrestaChamps <support@prestachamps.com>
 *  @copyright 2007-2026 PrestaChamps
 *  @license   https://opensource.org/licenses/AFL-3.0  Academic Free License 3.0 (AFL-3.0)
 *
 * FO CDN health observer — reports whether the shop's own MC.js loader
 * executed, by waiting for the $mcSite global it defines and matching the
 * connected-site key it carries against the cached loader URL. Each outcome
 * is reported once per browser session, so a session that reported a failure
 * can still report the recovery.
 */
(function () {
    'use strict';

    if (typeof window.mcCdnHealthConfig === 'undefined') {
        return;
    }
    var cfg = window.mcCdnHealthConfig;
    if (!cfg.probeUrl || !cfg.beaconUrl || !cfg.visitorHash) {
        return;
    }
    var prevOutcome = null;
    try {
        if (window.sessionStorage) {
            prevOutcome = sessionStorage.getItem('mc-cdn-outcome');
        }
    } catch (e) {
        // Private mode / quota — proceed without persistence.
    }

    // Last path segment of the cached loader URL is the connected-site key.
    var expectedKey = cfg.probeUrl.split('/').pop().replace(/\.js.*$/, '');
    if (!expectedKey) {
        return;
    }

    var POLL_MS = 200;
    var DEADLINE_MS = 8000;
    var deadline = 0;

    /**
     * null  — MC.js has not executed yet
     * ''    — it executed but exposes no connected-site key
     * other — the key of whichever loader defined $mcSite
     *
     * Deliberately not $mcSite.pixel.installed: that flips only after visitor
     * consent AND a second script from a different host, so it stays false on
     * a perfectly healthy shop whose visitor declined cookies.
     */
    function loadedKey() {
        var site = window.$mcSite;
        if (!site) {
            return null;
        }
        if (site.pixel && site.pixel.settings && site.pixel.settings.context_publicConnectedSiteId) {
            return String(site.pixel.settings.context_publicConnectedSiteId);
        }

        return '';
    }

    function tick() {
        var key = loadedKey();
        if (null !== key) {
            if ('' === key || key === expectedKey) {
                beacon('success');
            }
            // A different key means another MC.js snippet won the race and
            // this shop's own loader never got to run, so its health is
            // unknown — reporting either outcome would be a guess.
            return;
        }
        if (Date.now() >= deadline) {
            beacon('failure');

            return;
        }
        setTimeout(tick, POLL_MS);
    }

    function start() {
        deadline = Date.now() + DEADLINE_MS;
        tick();
    }

    if (document.readyState === 'complete') {
        start();
    } else {
        window.addEventListener('load', start);
    }

    function beacon(kind) {
        if (prevOutcome === kind) {
            // Already reported this outcome in this session. Only a change of
            // outcome is news — that is what lets a session which reported a
            // failure go on to report the recovery.
            return;
        }
        prevOutcome = kind;
        try {
            if (window.sessionStorage) {
                sessionStorage.setItem('mc-cdn-outcome', kind);
            }
        } catch (e) {}
        var params = 'kind=' + kind + '&vh=' + encodeURIComponent(cfg.visitorHash);
        if ('failure' === kind) {
            // No readable HTTP status: the loader is cross-origin, so the
            // browser reports neither its status nor its size.
            params += '&status=0';
        }
        if (navigator.sendBeacon) {
            try {
                var blob = new Blob([params], { type: 'application/x-www-form-urlencoded' });
                if (navigator.sendBeacon(cfg.beaconUrl, blob)) {
                    return;
                }
            } catch (e) {}
        }
        if (typeof fetch === 'function') {
            fetch(cfg.beaconUrl, {
                method: 'POST',
                body: params,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                keepalive: true,
            }).catch(function () {});
        }
    }
})();
