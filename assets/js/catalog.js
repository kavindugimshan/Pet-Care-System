/**
 * catalog.js — Pet Care System
 * Client-side UX enhancements for the customer service catalog.
 *
 * Responsibilities:
 *   1. Mobile navigation toggle (hamburger menu)
 *   2. Service image error fallback (onerror → placeholder)
 *   3. Filter form: animate the Clear Filters link
 *   4. Smooth scroll to results after filter submit
 *
 * The main search/filter logic is entirely server-side (PHP/MySQL).
 * This file only enhances the UI progressively — the site works
 * without JavaScript.
 */

(function () {
    'use strict';

    /* ── 1. Mobile Navigation Toggle ──────────────────────── */
    var navToggle = document.getElementById('navToggle');
    var siteNav   = document.querySelector('.site-nav');

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', function () {
            var isOpen = siteNav.classList.toggle('nav-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close nav when a link inside it is clicked (mobile UX)
        siteNav.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                siteNav.classList.remove('nav-open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Close nav when clicking outside
        document.addEventListener('click', function (e) {
            if (!navToggle.contains(e.target) && !siteNav.contains(e.target)) {
                siteNav.classList.remove('nav-open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ── 2. Service Image Error Fallback ───────────────────── */
    /**
     * Replace broken service images with the local placeholder SVG.
     * Attached as an onerror handler on each .service-img element.
     */
    function attachImageFallbacks() {
        var images = document.querySelectorAll('.service-img');
        images.forEach(function (img) {
            // Guard: already replaced or placeholder already set
            if (img.dataset.fallbackAttached) { return; }
            img.dataset.fallbackAttached = 'true';

            img.addEventListener('error', function () {
                var placeholder = img.closest('[data-base-path]')
                    ? img.closest('[data-base-path]').dataset.basePath
                    : '';
                img.src = placeholder + 'assets/images/placeholder.svg';
                img.alt = 'Service image not available';
                // Prevent infinite error loop if placeholder itself fails
                img.onerror = null;
            });
        });
    }

    /* ── 3. Filter Form UX ─────────────────────────────────── */
    function initFilterForm() {
        var form = document.getElementById('filterForm');
        if (!form) { return; }

        // Auto-submit on select change for a snappier feel (optional UX).
        // Commented out intentionally — keeps accessibility for keyboard users.
        // form.querySelectorAll('select').forEach(function (sel) {
        //     sel.addEventListener('change', function () { form.submit(); });
        // });

        // Highlight filter button briefly on submit
        var submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.textContent = 'Searching…';
                submitBtn.disabled = true;
            });
        }
    }

    /* ── 4. Smooth Scroll to Results ───────────────────────── */
    function scrollToResults() {
        // If URL has search params (i.e. filters were applied), scroll
        // down past the filter panel to the results automatically.
        if (window.location.search && window.location.search.length > 1) {
            var resultsAnchor = document.getElementById('results');
            if (resultsAnchor) {
                // Small delay so browser first finishes layout
                setTimeout(function () {
                    resultsAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            }
        }
    }

    /* ── Init ───────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        attachImageFallbacks();
        initFilterForm();
        scrollToResults();
    });

})();
